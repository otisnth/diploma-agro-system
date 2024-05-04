<?php

namespace App\Http\Controllers\Api;

use App\Models\TechnicType;
use Orion\Http\Controllers\Controller;
use Orion\Concerns\DisablePagination;
use Symfony\Component\HttpFoundation\Response;

class TechnicTypeController extends Controller
{
    use DisablePagination;

    protected $model = TechnicType::class;

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
                    'title' => 'Иконка',
                    'key' => 'icon',
                    'type' => 'image',
                    'required' => 'true'
                ],
            ]
        ], Response::HTTP_OK);
    }
}
