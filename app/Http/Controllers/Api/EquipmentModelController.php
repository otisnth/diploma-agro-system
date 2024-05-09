<?php

namespace App\Http\Controllers\Api;

use App\Models\EquipmentModel;
use App\Models\EquipmentType;
use Orion\Http\Controllers\Controller;
use Orion\Concerns\DisablePagination;
use Symfony\Component\HttpFoundation\Response;
use Orion\Http\Requests\Request;


class EquipmentModelController extends Controller
{
    use DisablePagination;

    protected $model = EquipmentModel::class;

    protected function afterIndex(Request $request, $entities)
    {
        $newCollection = $entities->map(function ($item, $key) {
            $item->type_id = EquipmentType::find($item->type_id)->name;
            return $item;
        });

        return $newCollection;
    }

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
                    'title' => 'Рабочая скорость',
                    'key' => 'work_speed',
                    'type' => 'number',
                    'inputProperties'=> [
                        'suffix' => 'км/ч',
                        'min' => 0,
                        'max' => 100
                    ],
                    'required' => 'true'
                ],
                [
                    'title' => 'Рабочая ширина',
                    'key' => 'work_width',
                    'type' => 'number',
                    'inputProperties'=> [
                        'suffix' => 'м',
                        'min' => 0,
                        'max' => 200
                    ],
                    'required' => 'true',
                ],
                [
                    'title' => 'Тип оборудования',
                    'key' => 'type_id',
                    'type' => 'select',
                    'required' => 'true',
                    'source' => 'equipment-types'
                ],
            ]
        ], Response::HTTP_OK);
    }
}
