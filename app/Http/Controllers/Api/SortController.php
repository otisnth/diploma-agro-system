<?php

namespace App\Http\Controllers\Api;

use App\Models\Sort;
use Orion\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;


class SortController extends Controller
{

    protected $model = Sort::class;

    public function properties()
    {
        return response()->json([
            'data' => [
                [
                    'title' => 'Название',
                    'key' => 'name',
                    'type' => 'text',
                    'required' => 'true'
                ], 
                [
                    'title' => 'Комфортная температура',
                    'key' => 'temperature',
                    'type' => 'number',
                    'inputProperties'=> [
                        'suffix' => '℃',
                        'min' => -40,
                        'max' => 40
                    ],
                    'required' => 'true'
                ],
                [
                    'title' => 'Комфортная влажность',
                    'key' => 'humidity',
                    'type' => 'number',
                    'inputProperties'=> [
                        'suffix' => '%',
                        'min' => 0,
                        'max' => 100
                    ],
                    'required' => 'true'
                ],
                [
                    'title' => 'Длительность вегетационного периода',
                    'key' => 'vegetation_period',
                    'type' => 'number',
                    'inputProperties'=> [
                        'suffix' => ' дня(ей)',
                    ],
                    'required' => 'true'
                ],
                [
                    'title' => 'Культура',
                    'key' => 'plant_id',
                    'type' => 'select',
                    'required' => 'true',
                    'source' => 'plants'
                ],
            ]
        ], Response::HTTP_OK);
    }

}
