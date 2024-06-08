<?php

namespace App\Http\Controllers\Api;

use App\Models\Plant;
use Orion\Http\Controllers\Controller;
use Orion\Concerns\DisablePagination;
use Symfony\Component\HttpFoundation\Response;
use App\Policies\TruePolicy;


class PlantController extends Controller
{
    use DisablePagination;

    protected $model = Plant::class;

    protected $policy = TruePolicy::class;

    public function properties()
    {
        return response()->json([
            'data' => [
                [
                    'title' => 'Название',
                    'key' => 'name',
                    'type' => 'text',
                    'required' => 'true',
                    'sortable'=> true,
                ],
                [
                    'title' => 'Цвет',
                    'key' => 'color',
                    'type' => 'color',
                    'required' => 'true',
                    'sortable'=> false
                ],
                [
                    'title' => 'Погодные условия',
                    'key' => 'weather_rules',
                    'type' => 'weather',
                    'indexHidden' => true,
                    'value' => [
                        'seeding' => [
                            'title' => 'Посев',
                            'propeties' => [
                                'humidity' => [
                                    'title' => 'Влажность',
                                    'ideal_value' => 0,
                                    'weight' => 0
                                ],
                                'temperature' => [
                                    'title' => 'Температура',
                                    'ideal_value' => 0,
                                    'weight' => 0
                                ],
                            ]
                        ],
                        'harvest' => [
                            'title' => 'Уборка',
                            'propeties' => [
                                'humidity' => [
                                    'title' => 'Влажность',
                                    'ideal_value' => 0,
                                    'weight' => 0
                                ]
                            ]
                        ]

                    ],
                ]
            ],
            'filters' => [
                'name' => [ 'value' => null, 'matchMode' => 'CONTAINS' ],
            ]
        ], Response::HTTP_OK);
    }

}
