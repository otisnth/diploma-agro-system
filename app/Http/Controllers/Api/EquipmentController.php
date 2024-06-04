<?php

namespace App\Http\Controllers\Api;

use App\Models\Equipment;
use App\Models\EquipmentModel;
use Orion\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;
use Orion\Http\Requests\Request;
use Illuminate\Database\Eloquent\Builder;
use App\Policies\TruePolicy;

class EquipmentController extends Controller
{

    protected $model = Equipment::class;

    protected $policy = TruePolicy::class;

    protected function runIndexFetchQuery(Request $request, Builder $query, int $paginationLimit)
    {   
        if ($request->query("limit") == "all") {
           return $query->get();
        }
        else {
            return $this->shouldPaginate($request, $paginationLimit) ? $query->paginate($paginationLimit) : $query->get();
        }
    }

    protected function afterIndex(Request $request, $entities)
    {
        $newCollection = $entities->map(function ($item, $key) {
            $item->model_id = EquipmentModel::find($item->model_id)->name;
            return $item;
        });

        return $newCollection;
    }

    public function properties()
    {
        return response()->json([
            'data' => [
                [
                    'title' => 'Модель оборудования',
                    'key' => 'model_id',
                    'type' => 'select',
                    'required' => 'true',
                    'source' => 'equipment-models',
                    'sortable'=> true
                ],
                [
                    'title' => 'Бортовой номер',
                    'key' => 'marking',
                    'type' => 'number',
                    'inputProperties'=> [
                    ],
                    'required' => 'true',
                    'sortable'=> true,
                ],
            ],
            'filters' => [
                'marking' => [ 'value' => null, 'matchMode' => 'CONTAINS' ],
                'model_id' => [ 'value' => null, 'matchMode' => 'EQUALS' ],
            ]
        ], Response::HTTP_OK);
    }

}
