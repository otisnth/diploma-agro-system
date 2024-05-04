<?php

namespace App\Http\Controllers\Api;

use App\Models\Plant;
use Orion\Http\Controllers\Controller;
use Orion\Concerns\DisablePagination;
use Symfony\Component\HttpFoundation\Response;


class PlantController extends Controller
{
    use DisablePagination;

    protected $model = Plant::class;

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
                    'title' => 'Цвет',
                    'key' => 'color',
                    'type' => 'color',
                    'required' => 'true'
                ],
            ]
        ], Response::HTTP_OK);
    }

}
