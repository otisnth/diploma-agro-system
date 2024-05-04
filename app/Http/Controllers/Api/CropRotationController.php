<?php

namespace App\Http\Controllers\Api;

use App\Models\CropRotation;
use Orion\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;

class CropRotationController extends Controller
{

    protected $model = CropRotation::class;

    public function properties()
    {
        return response()->json([
            'data' => [
                [
                    'title' => 'Дата посева',
                    'key' => 'start_date',
                    'type' => 'date',
                    'required' => 'true'
                ], 
                [
                    'title' => 'Дата уборки',
                    'key' => 'end_date',
                    'type' => 'date',
                    'required' => 'false'
                ],
                [
                    'title' => 'Поле',
                    'key' => 'field_id',
                    'type' => 'select',
                    'required' => 'true',
                    'source' => 'fields'
                ],
                [
                    'title' => 'Сорт',
                    'key' => 'sort_id',
                    'type' => 'select',
                    'required' => 'true',
                    'source' => 'sorts'
                ],
            ]
        ], Response::HTTP_OK);
    }
}
