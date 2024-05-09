<?php

namespace App\Http\Controllers\Api;

use App\Models\Sort;
use App\Models\Plant;
use Orion\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;
use Orion\Http\Requests\Request;
use Illuminate\Database\Eloquent\Builder;


class SortController extends Controller
{

    protected $model = Sort::class;

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
