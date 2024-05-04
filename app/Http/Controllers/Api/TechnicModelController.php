<?php

namespace App\Http\Controllers\Api;

use App\Models\TechnicModel;
use Orion\Http\Controllers\Controller;
use Orion\Concerns\DisablePagination;
use Symfony\Component\HttpFoundation\Response;


class TechnicModelController extends Controller
{
    use DisablePagination;
    protected $model = TechnicModel::class;

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
                    'title' => 'Тип техники',
                    'key' => 'type_id',
                    'type' => 'select',
                    'required' => 'true',
                    'source' => 'technic-types'
                ],
            ]
        ], Response::HTTP_OK);
    }
}
