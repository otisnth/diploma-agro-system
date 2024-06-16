<?php

namespace App\Http\Controllers\Api;

use App\Models\WorkerUnit;
use Orion\Http\Controllers\Controller;
use Orion\Concerns\DisablePagination;
use App\Policies\TruePolicy;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use DateTime;

use MrWolfGb\Traccar\Services\TraccarService;

class WorkerUnitController extends Controller
{
    use DisablePagination;

    protected $model = WorkerUnit::class;

    protected $policy = TruePolicy::class;

    public function includes(): array
    {
        return ['operationNote', 'operationNote.field', 'technic', 'technic.model', 'technic.model.type', 'equipments', 'equipments.model'];
    }

    public function filterableBy(): array
    {
        return ['is_used', 'worker_id', 'operation_note_id'];
    }

    protected function beforeUpdate(Request $request, Model $entity)
    {

        if ($request->complete_confirm && !$entity->complete_confirm && !$entity->end_date) {
            $entity->end_date = date('Y-m-d H:i:s');
        }

        if ($request->is_used && !$entity->is_used && !$entity->start_date) {
            $entity->start_date = date('Y-m-d H:i:s');

            $workerUnits = $entity->worker->workerUnits;

            $workerUnits->each(function ($workerUnit) {
                $workerUnit->is_used = false;
                $workerUnit->save();
            });
        }
    }

    protected function afterUpdate(Request $request, Model $entity)
    {
        $operationNote = $entity->operationNote;

        if ($request->complete_confirm) {

            $workersUnits = $operationNote->workerUnits;

            $completeWorkerUnits = $workersUnits->where('complete_confirm', true);

            if ($workersUnits->count() == $completeWorkerUnits->count()) {
                $operationNote->update(['status' => 'awaitConfirm']);
            }



            try {
                $minWorkWidth = null;
                $minWorkSpeed = null;


                foreach ($entity->equipments as $equipment) {
                    if (isset($equipment->model)) {
                        $workWidth = floatval($equipment->model->work_width);
                        $workSpeed = floatval($equipment->model->work_speed);

                        if ($minWorkSpeed === null || $workSpeed < $minWorkSpeed) {
                            $minWorkSpeed = $workSpeed;
                        }

                        if ($minWorkWidth === null || $workWidth < $minWorkWidth) {
                            $minWorkWidth = $workWidth;
                        }
                    }
                }
                $traccarService = new TraccarService(
                    baseUrl: config('traccar.base_url'),
                    email: config('traccar.auth.username'),
                    password: config('traccar.auth.password'),
                    token: config('traccar.auth.token'),
                    headers: [
                        'Accept' => 'application/json'
                    ]
                );

                $reportRepo = $traccarService->reportRepository();
                $deviceRepo = $traccarService->deviceRepository();

                $device = $deviceRepo->getDeviceByUniqueId(uniqueId: $entity->technic->tr_device_id);

                $summaryRep = $reportRepo->reportSummary(
                    from: $entity->start_date,
                    to: $entity->end_date,
                    deviceId: [$device->id]
                );

                $combinedRep = $reportRepo->reportCombined(
                    from: $entity->start_date,
                    to: $entity->end_date,
                    deviceId: [$device->id],
                );

                $events = $combinedRep[0]->events;

                $startTime = new DateTime(current($events)['eventTime']);
                $endTime = new DateTime(end($events)['eventTime']);
                $distance = $summaryRep[0]->distance;
                $speed = $summaryRep[0]->speed * 1.852;
                $route = $combinedRep[0]->route;

                $interval = $startTime->diff($endTime);

                $hoursDifference = round(($interval->days * 24) + $interval->h + ($interval->i / 60) + ($interval->s / 3600), 2);
                $square = round($distance * $minWorkWidth);
                $efficiency = round($square / $hoursDifference, 2);

                $plannedTime = round($entity->operationNote->field->square / ($minWorkWidth * $minWorkSpeed * 1000), 2);
                $usedTime = round($hoursDifference / $plannedTime, 2);

                $stopTime = rand(0, $hoursDifference / 5);
                $stopPart = round($stopTime / $hoursDifference, 4);

                $total = $efficiency * 0.001 + $usedTime * 10 + $stopPart * 100;

                $report = [
                    'p' => [
                        'square' => $square,
                        'time' => $hoursDifference,
                        'value' => $efficiency
                    ],
                    't' => [
                        'fact' => $hoursDifference,
                        'plan' => $plannedTime,
                        'value' => $usedTime
                    ],
                    'd' => [
                        'stop' => $stopTime,
                        'total' => $hoursDifference,
                        'value' => $stopPart
                    ],
                    'total' => $total,
                    'route' => $route
                ];

                $entity->report = json_encode($report);
                $entity->save();
            } catch (\Throwable $th) {
                //throw $th;
            }
        }

        if ($request->is_used && $operationNote->status == 'assigned') {
            $operationNote->update(['status' => 'inProgress']);
        }
    }
}
