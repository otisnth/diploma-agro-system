<?php

namespace App\Http\Controllers\Api;

use App\Models\CropRotation;
use App\Policies\TruePolicy;
use Orion\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;
use Orion\Http\Requests\Request;
use Illuminate\Database\Eloquent\Model;

class CropRotationController extends Controller
{

    protected $model = CropRotation::class;

    protected $policy = TruePolicy::class;

    public function includes() : array
    {
        return ['sort', 'sort.plant'];
    }

    public function filterableBy(): array
    {
        return ['field_id'];
    }

    public function sortableBy(): array
    {
        return ['start_date', 'end_date'];
    }

    protected function beforeStore(Request $request, Model $entity)
    {
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        $overlappingCropRotations = CropRotation::where(function($query) use ($start_date, $end_date) {
            $query->where('start_date', '<', $end_date)
                  ->where('end_date', '>', $start_date);
        })->exists();

        if ($overlappingCropRotations) {
            return response()->json(['error' => 'Введенный период пересекается с существующим'], 422);
        }

    }

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
