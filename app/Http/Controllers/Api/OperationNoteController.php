<?php

namespace App\Http\Controllers\Api;

use App\Models\OperationNote;
use App\Models\Field;
use App\Models\Plant;
use App\Models\Sort;
use App\Models\Equipment;
use App\Models\EquipmentModel;
use App\Models\CropRotation;
use Orion\Http\Controllers\Controller;
use App\Policies\TruePolicy;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Orion\Http\Requests\Request as OrionRequest;
use Illuminate\Database\Eloquent\Model;
use RakibDevs\Weather\Weather;
use DateTime;

class OperationNoteController extends Controller
{

    protected $model = OperationNote::class;

    protected $policy = TruePolicy::class;

    public function includes(): array
    {
        return ['field', 'author', 'sort', 'sort.plant'];
    }

    public function filterableBy(): array
    {
        return ['field_id', 'status', 'operation', 'field.name', 'author.name', 'created_by', 'workerUnits.worker_id'];
    }

    public function sortableBy(): array
    {
        return ['start_date', 'end_date'];
    }

    public function recommendations(Request $request): Response
    {
        $field = Field::find($request->field);
        $operation = $request->operation;

        if ($operation == 'harvest') {
            $plant = $field->sort->plant;
        } else {
            $plant = Plant::find($request->plant);
        }


        if ($plant) {
            $weatherRules = $plant->weather_rules;
        }

        $coords = $field->coords['geometry']['coordinates'][0][0];

        $wt = new Weather();
        $info = $wt->get3HourlyByCord($coords[1], $coords[0]);

        $filterByHour = function ($object) {
            $parts = explode(' ', $object->dt);
            return isset($parts[1]) && $parts[1] === '12:00';
        };

        $weather = array_filter($info->list, $filterByHour);

        $startDate = DateTime::createFromFormat('d/m/Y H:i', current($weather)->dt)->format('d.m');
        $endDate = DateTime::createFromFormat('d/m/Y H:i', end($weather)->dt)->format('d.m');
        $recommendation = 'В период с ' . $startDate . ' по ' . $endDate;

        $datesArray = array();


        if ($operation == "spraying" || $operation == "fertilization") {
            $findedDate = false;

            foreach ($weather as $value) {
                if ($value->wind->speed >= 4) {
                    $ci = array(
                        'date' => DateTime::createFromFormat('d/m/Y H:i', $value->dt)->format('d.m'),
                        'value' => INF
                    );
                } else {
                    $ci = array(
                        'date' => DateTime::createFromFormat('d/m/Y H:i', $value->dt)->format('d.m'),
                        'value' => $value->wind->speed
                    );
                }

                array_push($datesArray, $ci);
            }

            foreach ($datesArray as $date) {
                if ($date['value'] != INF) {
                    $recommendation = $recommendation . ' оптимальной датой для проведения данного мероприятия является ' . $date['date'];
                    $findedDate = true;
                    break;
                }
            }

            if (!$findedDate) {
                $recommendation = $recommendation . ' отсутствуют благоприятные даты для проведения данного мероприятия. В данном промежутке наблюдается скорость ветра выше рекомендуемой.';
            }
        } else if ($operation == "harvest") {
            $humWeight = $weatherRules[$operation]['propeties']['humidity']['weight'];
            $humIdeal = $weatherRules[$operation]['propeties']['humidity']['ideal_value'];

            foreach ($weather as $day) {
                $ci = array(
                    'date' => DateTime::createFromFormat('d/m/Y H:i', $day->dt)->format('d.m'),
                    'value' => NULL
                );

                foreach ($day->weather as $wh) {
                    if ($wh->main == 'Rain' || $wh->main == 'Snow') {
                        $ci['value'] = INF;
                        break;
                    }
                }

                if ($ci['value'] == INF) {
                    array_push($datesArray, $ci);
                    continue;
                }

                $dayHum = $day->main->humidity;
                $ci['value'] = sqrt($humWeight * pow(($dayHum - $humIdeal), 2));

                array_push($datesArray, $ci);
            }

            $minValueElement = array_reduce($datesArray, function ($carry, $item) {
                if ($carry === null || $item['value'] < $carry['value']) {
                    return $item;
                }
                return $carry;
            });

            if ($minValueElement['value'] == INF) {
                $recommendation = $recommendation . ' отсутствуют благоприятные даты для проведения данного мероприятия. В данном промежутке каждый день наблюдаются осадки.';
            } else {
                $recommendation = $recommendation . ' оптимальной датой для проведения данного мероприятия является ' . $minValueElement['date'] . '. В этот день наблюдается наименьшее отклонение от идеальных показателей.';
            }
        } else {
            $humWeight = $weatherRules[$operation]['propeties']['humidity']['weight'];
            $humIdeal = $weatherRules[$operation]['propeties']['humidity']['ideal_value'];

            $tempWeight = $weatherRules[$operation]['propeties']['temperature']['weight'];
            $tempIdeal = $weatherRules[$operation]['propeties']['temperature']['ideal_value'];

            foreach ($weather as $day) {
                $ci = array(
                    'date' => DateTime::createFromFormat('d/m/Y H:i', $day->dt)->format('d.m'),
                    'value' => NULL
                );

                $dayHum = $day->main->humidity;
                $dayTemp = $day->main->temp;

                $ci['value'] = sqrt(($humWeight * pow(($dayHum - $humIdeal), 2)) + ($tempWeight * pow(($dayTemp - $tempIdeal), 2)));

                array_push($datesArray, $ci);
            }

            $minValueElement = array_reduce($datesArray, function ($carry, $item) {
                if ($carry === null || $item['value'] < $carry['value']) {
                    return $item;
                }
                return $carry;
            });

            $recommendation = $recommendation . ' оптимальной датой для проведения данного мероприятия является ' . $minValueElement['date'] . '. В этот день наблюдается наименьшее отклонение от идеальных показателей.';
        }

        $periodDates = array();
        $values = array();


        foreach ($datesArray as $dateVal) {
            array_push($values, $dateVal['value']);
            array_push($periodDates, $dateVal['date']);
        }

        function maxWithoutInf($array)
        {
            $filteredArray = array_filter($array, function ($value) {
                return $value !== INF;
            });

            if (empty($filteredArray)) {
                return 100;
            }

            return round(max($filteredArray));
        }

        $maxVal = maxWithoutInf($values);

        foreach ($values as $i => $ival) {
            if ($ival == INF) {
                $values[$i] = $maxVal;
            }
        }

        return response()->json([
            'data' => [
                'recommendation' => $recommendation,
                'period' => $periodDates,
                'values' => $values
            ],

        ], Response::HTTP_OK);
    }

    public function expectedTime(Request $request): Response
    {
        $fieldId = $request->field;
        $equipmentsIds = $request->equipments;

        $field = Field::find($fieldId);

        $properties = array();

        foreach ($equipmentsIds as $ids) {
            $equipments = Equipment::whereIn('id', $ids)->get();
            $minWorkSpeed = null;
            $minWorkWidth = null;

            foreach ($equipments as $equipment) {
                if (isset($equipment->model)) {
                    $workSpeed = floatval($equipment->model->work_speed);
                    $workWidth = floatval($equipment->model->work_width);

                    if ($minWorkSpeed === null || $workSpeed < $minWorkSpeed) {
                        $minWorkSpeed = $workSpeed;
                    }
                    if ($minWorkWidth === null || $workWidth < $minWorkWidth) {
                        $minWorkWidth = $workWidth;
                    }
                }
            }

            if ($minWorkSpeed && $minWorkWidth) {
                array_push($properties, ['width' => $minWorkWidth, 'speed' => $minWorkSpeed]);
            }
        }
        $efficiency = 0;

        foreach ($properties as $prop) {
            $efficiency += $efficiency + $prop['width'] * $prop['speed'] * 1000;
        }

        $efficiency = round($efficiency, 2);

        $time = round($field->square / $efficiency, 2);

        return response()->json([
            'data' => [
                'expectedTime' => [
                    'efficiency' => $efficiency,
                    'time' => $time
                ]
            ],

        ], Response::HTTP_OK);
    }

    protected function performStore(OrionRequest $request, Model $entity, array $attributes): void
    {
        $attributes['created_by'] = Auth()->user()->id;
        $this->performFill($request, $entity, $attributes);
        $entity->save();
    }

    protected function afterStore(Request $request, Model $entity)
    {

        if ($request['operation'] == "seeding") {
            if ($request['start_date']) {
                $sort = Sort::find($request['sort_id']);
                $date = new DateTime($request['start_date']);
                $date->modify('+' . $sort->vegetation_period . ' days');
            } else {
                $date = null;
            }
            $operationNote = new OperationNote;

            $operationNote['start_date'] = $date;
            $operationNote['status'] = "planned";
            $operationNote['operation'] = "harvest";
            $operationNote['field_id'] = $entity['field_id'];
            $operationNote['created_by'] = $entity['created_by'];

            $operationNote->save();
        }
    }

    protected function afterSave(Request $request, Model $entity)
    {
        $workerUnits = $entity->workerUnits;

        if ($entity->status == 'planned' && $entity->start_date && $workerUnits->count() > 0) {
            $entity->update(['status' => 'assigned']);
        } else if ($entity->status == 'assigned' && (!$entity->start_date || $workerUnits->count() == 0)) {
            $entity->update(['status' => 'planned']);
        }
    }

    protected function beforeUpdate(Request $request, Model $entity)
    {
        $workersUnits = $entity->workerUnits;

        if ($request->status == 'canceled') {
            $entity->end_date = date('Y-m-d H:i:s');
            $workerUnitsUsed = $workersUnits->where('is_used', true);
            if ($workerUnitsUsed->count() > 0) {
                return response()->json(['error' => 'Невозможно отменить мероприятие, так как началось его выполнение'], 422);
            }
            $workersUnits->each(function ($workerUnit) {
                $workerUnit->complete_confirm = true;
                $workerUnit->save();
            });
        }

        if ($request->status == 'completed') {
            $entity->end_date = date('Y-m-d H:i:s');
            $workersUnits->each(function ($workerUnit) {
                $workerUnit->complete_confirm = true;
                $workerUnit->save();
            });
            try {
                if ($entity->operation == 'harvest') {
                    $entity->field->update(['sort_id' => null]);
                    $cropRotation = $entity->field->cropHistory->whereNull('end_date')->first();
                    $cropRotation->update(['end_date' => date('Y-m-d H:i:s')]);
                }
            } catch (\Throwable $th) {
                //throw $th;
            }
        }
    }

    // protected function beforeSave(Request $request, Model $entity)
    // {
    //     $start_date = $request->start_date;
    //     $operation = $request->operation;
    //     $field_id = $request->field_id;

    //     $overlappingOperationNotes = OperationNote::where(function($query) use ($start_date, $operation, $field_id) {
    //         $query->where('operation', '=', $operation)
    //               ->whereNotNull('field_id')
    //               ->where('field_id', '=', $field_id)
    //               ->where(function($query) {
    //                     $query->whereNull('start_date')

    //                         ->orWhere();
    //               });
    //     })->exists();

    //     if ($overlappingOperationNotes) {
    //         return response()->json(['error' => 'Введенный период пересекается с существующим'], 422);
    //     }

    // }

}
