<?php

namespace App\Http\Controllers;

use App\Models\Field;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use DateTime;

use RakibDevs\Weather\Weather;

class MainPageController extends Controller
{
    

    public function index(Request $request): Response
    {
        $firstField = Field::first();

        if ($firstField)
        {
            $coords = $firstField->coords['geometry']['coordinates'][0][0];
        } else {
            $coords = array(52.609025, 39.598970);
        }


        $wt = new Weather();

        $info = $wt->get3HourlyByCord($coords[1], $coords[0]);

        $filterByHour = function ($object) {
            $parts = explode(' ', $object->dt);
            return isset($parts[1]) && $parts[1] === '12:00';
        };

        function degreesToDirection($degrees) {
            $directions = [
                'С' => [0, 11.25],
                'ССВ' => [11.25, 33.75],
                'СВ' => [33.75, 56.25],
                'ВСВ' => [56.25, 78.75],
                'В' => [78.75, 101.25],
                'ВЮВ' => [101.25, 123.75],
                'ЮВ' => [123.75, 146.25],
                'ЮЮВ' => [146.25, 168.75],
                'Ю' => [168.75, 191.25],
                'ЮЮЗ' => [191.25, 213.75],
                'ЮЗ' => [213.75, 236.25],
                'ЗЮЗ' => [236.25, 258.75],
                'З' => [258.75, 281.25],
                'ЗСЗ' => [281.25, 303.75],
                'СЗ' => [303.75, 326.25],
                'ССЗ' => [326.25, 348.75],
                'С' => [348.75, 360]
            ];
        
            foreach ($directions as $direction => $range) {
                if ($degrees >= $range[0] && $degrees < $range[1]) {
                    return $direction;
                }
            }
        
            return 'С';
        }

        $weather = array_filter($info->list, $filterByHour);

        $daysOfWeek = ['воскресенье', 'понедельник', 'вторник', 'среда', 'четверг', 'пятница', 'суббота'];
        $month = ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'];

        $forecast = array();

        foreach ($weather as $value) {
            $date = $month[DateTime::createFromFormat('d/m/Y H:i', $value->dt)->format('n') - 1] . ' ' . DateTime::createFromFormat('d/m/Y H:i', $value->dt)->format('d');
            $weekday = $daysOfWeek[DateTime::createFromFormat('d/m/Y H:i', $value->dt)->format('w')];

            $tempObj = array(
                'date' => $date,
                'weekday' => $weekday,
                'temp' => round($value->main->temp, 1) . "°С",
                'feels_like' => round($value->main->feels_like, 1) . "°С",
                'humidity' => $value->main->humidity . "%",
                'windDir' => degreesToDirection($value->wind->deg),
                'windSpeed' => round($value->wind->speed, 1) . " м/с",
                'icon' => 'https://openweathermap.org/img/wn/' . $value->weather[0]->icon . '@2x.png',
            );

            array_push($forecast, $tempObj);
        }

        return Inertia::render('Dashboard', [
            'forecast' => $forecast,
        ]);
    }

}
