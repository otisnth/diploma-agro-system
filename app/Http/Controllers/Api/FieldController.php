<?php

namespace App\Http\Controllers\Api;

use Exception;

use App\Models\Field;
use App\Models\CropRotation;
use Orion\Http\Controllers\Controller;
use Orion\Http\Requests\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpFoundation\Response;
use App\Policies\TruePolicy;

use MrWolfGb\Traccar\Services\TraccarService;

class FieldController extends Controller
{
    protected $model = Field::class;

    protected $policy = TruePolicy::class;

    public function includes() : array
    {
        return ['sort', 'sort.plant'];
    }

    public function filterableBy(): array
    {
        return ['name', 'id'];
    }

    public function sortableBy(): array
    {
        return ['name', 'square', 'status', 'sort.plant.name', 'sort.name'];
    }

    protected function runIndexFetchQuery(Request $request, Builder $query, int $paginationLimit)
    {
        if ($request->limit == "all") {
            return $query->get();
        }
        else {
            return $this->shouldPaginate($request, $paginationLimit) ? $query->paginate($paginationLimit) : $query->get();
        }
    }

    protected function geoJsonToWkt($geoJson)
    {
        $data = $geoJson['geometry'];

        if (isset($data['type']) && $data['type'] === 'Polygon' && isset($data['coordinates'])) {
            $coordinates = $data['coordinates'][0];
            
            $wktCoordinates = array_map(function($coord) {
                return "{$coord[1]} {$coord[0]}";
            }, $coordinates);
            
            $wktString = 'POLYGON ((' . implode(', ', $wktCoordinates) . '))';

            return $wktString;
        } else {
            throw new Exception("Неверный формат GeoJSON или отсутствуют координаты.");
        }
    }

    protected function addToTraccar($name, $area)
    {

        $traccarService = new TraccarService(
            baseUrl: config('traccar.base_url'),
            email: config('traccar.auth.username'),
            password: config('traccar.auth.password'),
            token: config('traccar.auth.token'),
            headers: [
                'Accept' => 'application/json'
            ]
        );
        $geofenceRepo = $traccarService->geofenceRepository();

        $geofence = $geofenceRepo->createGeofence(
            name: $name, 
            area: $area,
            description: $name
        );

        return $geofence;
    }

    protected function updateToTraccar($id, $area)
    {

        $traccarService = new TraccarService(
            baseUrl: config('traccar.base_url'),
            email: config('traccar.auth.username'),
            password: config('traccar.auth.password'),
            token: config('traccar.auth.token'),
            headers: [
                'Accept' => 'application/json'
            ]
        );
        $geofenceRepo = $traccarService->geofenceRepository();
        $list = $geofenceRepo->fetchListGeofences();

        $fence = $list->where('id', $id)->first();

        $fence->area = $area;

        $geofence = $geofenceRepo->updateGeofence($fence);
    }

    protected function removeFromTraccar($id)
    {
        $traccarService = new TraccarService(
            baseUrl: config('traccar.base_url'),
            email: config('traccar.auth.username'),
            password: config('traccar.auth.password'),
            token: config('traccar.auth.token'),
            headers: [
                'Accept' => 'application/json'
            ]
        );
        $geofenceRepo = $traccarService->geofenceRepository();

        $geofenceRepo->deleteGeofence($id);
    }

    protected function beforeStore(Request $request, Model $entity)
    {
        $area = $this->geoJsonToWkt($request->coords);
        $traccarGeoFence = $this->addToTraccar($request->name, $area);

        $request['tr_geofence_id'] = $traccarGeoFence->id;

    }

    protected function beforeUpdate(Request $request, Model $entity)
    {
        if ($request->tr_geofence_id) {
            $area = $this->geoJsonToWkt($request->coords);
            $this->updateToTraccar($request->tr_geofence_id, $area);
        }
    }

    protected function afterStore(Request $request, Model $entity)
    {
        if ($request['sort_id'])
        {
            $cropRotation = new CropRotation;

            $cropRotation['field_id'] = $entity['id'];
            $cropRotation['sort_id'] = $request['sort_id'];
            $cropRotation['start_date'] = $request['startDate'];

            $cropRotation->save();
        }

    }

    protected function afterDestroy(Request $request, Model $entity)
    {   
        if($entity->getAttribute("tr_geofence_id"))
        {
            $this->removeFromTraccar($entity->getAttribute("tr_geofence_id"));
        }
    }

    public function getFieldStatuses()
    {
        return response()->json([
            'data' => Field::$fieldStatuses
        ], Response::HTTP_OK);
    }

}
