<?php

namespace App\Http\Controllers\Api;

use App\Models\OperationNote;
use App\Models\Field;
use App\Models\Plant;
use App\Models\Sort;
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

    public function recommendations(Request $request): Response
    {
        $field = Field::find($request->field);
        $operation = $request->operation;

        if ($operation == 'harvest') {
            $plant = $field->sort->plant;
        }
        else {
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
                }
                else {
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
            }
            else {
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

        function maxWithoutInf($array) {
            $filteredArray = array_filter($array, function($value) {
                return $value !== INF;
            });
        
            if (empty($filteredArray)) {
                return 100;
            }
        
            return round(max($filteredArray));
        }

        $maxVal = maxWithoutInf($values);

        foreach ($values as $i => $ival) {
            if ($ival == INF) 
            {
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

    protected function performStore(OrionRequest $request, Model $entity, array $attributes): void
    {
        $attributes['created_by'] = Auth()->user()->id;
        $this->performFill($request, $entity, $attributes);
        $entity->save();
    }

    protected function afterSave(Request $request, Model $entity)
    {

        if ($request['operation'] == "seeding")
        {
            if ($request['start_date'])
            {
                $sort = Sort::find($request['sort_id']);
                $date = new DateTime($request['start_date']);
                $date->modify('+'.$sort->vegetation_period.' days');
            }
            else {
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

}
