<?php

namespace App\Http\Controllers\Api;

use App\Models\WorkerUnit;
use Orion\Http\Controllers\Controller;
use Orion\Concerns\DisablePagination;
use App\Policies\TruePolicy;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

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

            $list = $reportRepo->reportCombined(
                from: $entity->start_date,
                to: $entity->end_date,
                deviceId: [$device->id],
            );

            $distance = $summaryRep[0]->distance;
            $speed = $summaryRep[0]->speed * 1.852;
            $route = $list[0]->route;



            dd($list[0]->route);
        }

        if ($request->is_used && $operationNote->status == 'assigned') {
            $operationNote->update(['status' => 'inProgress']);
        }
    }
}
