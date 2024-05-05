<?php

namespace App\Http\Controllers\Api;

use App\Models\TechnicType;
use Orion\Http\Controllers\Controller;
use Orion\Concerns\DisablePagination;
use Symfony\Component\HttpFoundation\Response;
use Orion\Http\Requests\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TechnicTypeController extends Controller
{
    use DisablePagination;

    protected $model = TechnicType::class;

    protected function afterIndex(Request $request, $entities)
    {
        $newCollection = $entities->map(function ($item, $key) {
            $item->icon = $item->icon ? asset(Storage::url($item->icon)) : null;
            return $item;
        });
        
        return $newCollection;
    }

    protected function performStore(Request $request, Model $entity, array $attributes): void
    {

        if ($request->hasFile('icon')) {
            $icon = $request->file('icon')[0];
            $fileName = time() . '_' . Str::slug(pathinfo($icon->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . Str::slug($icon->getClientOriginalExtension());
            $path = $icon->storeAs('public/icons', $fileName);

            $attributes['icon'] = $path;
        }

        $this->performFill($request, $entity, $attributes);
        $entity->save();
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
                    'title' => 'Иконка',
                    'key' => 'icon',
                    'type' => 'image',
                    'required' => 'true'
                ],
            ]
        ], Response::HTTP_OK);
    }
}
