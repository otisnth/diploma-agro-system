<?php

namespace App\Http\Controllers\Api;

use App\Models\Sort;
use App\Models\Plant;
use Orion\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;
use Orion\Http\Requests\Request;
use Orion\Concerns\DisablePagination;
use Illuminate\Database\Eloquent\Builder;


class SortController extends Controller
{
    use DisablePagination;
    protected $model = Sort::class;

    public function includes() : array
    {
        return ['plant'];
    }

    public function filterableBy(): array
    {
        return ['plant_id'];
    }

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
            $item->plant_id = Plant::find($item->plant_id)->name;
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
                    'sortable'=> true
                ], 
                [
                    'title' => 'Комфортная температура',
                    'key' => 'temperature',
                    'type' => 'number',
                    'inputProperties'=> [
                        'suffix' => ' ℃',
                        'min' => -40,
                        'max' => 40
                    ],
                    'required' => 'true',
                    'sortable'=> true,
                ],
                [
                    'title' => 'Вес температуры',
                    'key' => 'temperature_weight',
                    'type' => 'number',
                    'inputProperties'=> [
                    ],
                    'required' => 'true',
                    'sortable'=> true,
                ],
                [
                    'title' => 'Комфортная влажность',
                    'key' => 'humidity',
                    'type' => 'number',
                    'inputProperties'=> [
                        'suffix' => ' %',
                        'min' => 0,
                        'max' => 100
                    ],
                    'required' => 'true',
                    'sortable'=> true
                ],
                [
                    'title' => 'Вес влажности',
                    'key' => 'humidity_weight',
                    'type' => 'number',
                    'inputProperties'=> [
                    ],
                    'required' => 'true',
                    'sortable'=> true,
                ],
                [
                    'title' => 'Длительность вегетационного периода',
                    'key' => 'vegetation_period',
                    'type' => 'number',
                    'inputProperties'=> [
                        'suffix' => ' дня(ей)',
                        'min' => 0,
                        'max' => 365
                    ],
                    'required' => 'true',
                    'sortable'=> true
                ],
                [
                    'title' => 'Культура',
                    'key' => 'plant_id',
                    'type' => 'select',
                    'required' => 'true',
                    'source' => 'plants',
                    'sortable'=> true
                ],
            ],
            'filters' => [
                'name' => [ 'value' => null, 'matchMode' => 'CONTAINS' ],
                'plant_id' => [ 'value' => null, 'matchMode' => 'EQUALS' ],
            ]
        ], Response::HTTP_OK);
    }

}
