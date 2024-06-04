<?php

namespace App\Http\Controllers\Api;

use App\Models\Technic;
use App\Models\TechnicModel;
use Orion\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;
use Orion\Http\Requests\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Policies\TruePolicy;

use MrWolfGb\Traccar\Services\TraccarService;

class TechnicController extends Controller
{

    protected $model = Technic::class;
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

    protected function addToTraccar($name, $uniqueId) {

        $traccarService = new TraccarService(
            baseUrl: config('traccar.base_url'),
            email: config('traccar.auth.username'),
            password: config('traccar.auth.password'),
            token: config('traccar.auth.token'),
            headers: [
                'Accept' => 'application/json'
            ]
        );
        $deviceRepo = $traccarService->deviceRepository();

        $device = $deviceRepo->createDevice(name: $name, uniqueId: $uniqueId);
    }

    protected function deleteFromTraccar($uniqueId) {

        $traccarService = new TraccarService(
            baseUrl: config('traccar.base_url'),
            email: config('traccar.auth.username'),
            password: config('traccar.auth.password'),
            token: config('traccar.auth.token'),
            headers: [
                'Accept' => 'application/json'
            ]
        );
        $deviceRepo = $traccarService->deviceRepository();

        $device = $deviceRepo->getDeviceByUniqueId(uniqueId: $uniqueId);
        $deviceRepo->deleteDevice(device: $device);
    }


    protected function beforeSave(Request $request, Model $entity)
    {
        $this->addToTraccar($request->license_plate, $request->tr_device_id);
    }

    protected function beforeDestroy(Request $request, Model $entity)
    {
        $this->deleteFromTraccar($entity->tr_device_id);
    }

    protected function afterIndex(Request $request, $entities)
    {
        $newCollection = $entities->map(function ($item, $key) {
            $item->model_id = TechnicModel::find($item->model_id)->name;
            return $item;
        });

        return $newCollection;
    }

    public function properties()
    {
        return response()->json([
            'data' => [
                [
                    'title' => 'Модель техники',
                    'key' => 'model_id',
                    'type' => 'select',
                    'required' => 'true',
                    'source' => 'technic-models',
                    'sortable'=> true
                ],
                [
                    'title' => 'Госномер',
                    'key' => 'license_plate',
                    'type' => 'text',
                    'required' => 'true',
                    'sortable'=> true
                ], 
                [
                    'title' => 'Идентификатор трекера',
                    'key' => 'tr_device_id',
                    'type' => 'number',
                    'inputProperties'=> [
                    ],
                    'required' => 'false',
                    'sortable'=> false,
                ],
            ],
            'filters' => [
                'license_plate' => [ 'value' => null, 'matchMode' => 'CONTAINS' ],
                'model_id' => [ 'value' => null, 'matchMode' => 'EQUALS' ],
            ]
        ], Response::HTTP_OK);
    }
}
