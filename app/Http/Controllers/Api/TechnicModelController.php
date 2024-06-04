<?php

namespace App\Http\Controllers\Api;

use App\Models\TechnicModel;
use App\Models\TechnicType;
use Orion\Http\Controllers\Controller;
use Orion\Concerns\DisablePagination;
use Symfony\Component\HttpFoundation\Response;
use Orion\Http\Requests\Request;
use App\Policies\TruePolicy;


class TechnicModelController extends Controller
{
    use DisablePagination;
    protected $model = TechnicModel::class;
    protected $policy = TruePolicy::class;


    protected function afterIndex(Request $request, $entities)
    {
        $newCollection = $entities->map(function ($item, $key) {
            $item->type_id = TechnicType::find($item->type_id)->name;
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
                    'required' => 'true',
                    'sortable'=> true,
                ], 
                [
                    'title' => 'Тип техники',
                    'key' => 'type_id',
                    'type' => 'select',
                    'required' => 'true',
                    'source' => 'technic-types',
                    'sortable'=> true
                ],
            ],
            'filters' => [
                'name' => [ 'value' => null, 'matchMode' => 'CONTAINS' ],
                'type_id' => [ 'value' => null, 'matchMode' => 'EQUALS' ],
            ]
        ], Response::HTTP_OK);
    }
}
