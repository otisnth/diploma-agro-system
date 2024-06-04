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
use App\Policies\TruePolicy;

class TechnicTypeController extends Controller
{
    use DisablePagination;

    protected $model = TechnicType::class;

    protected $policy = TruePolicy::class;

    protected function afterIndex(Request $request, $entities)
    {
        $newCollection = $entities->map(function ($item, $key) {
            $item->icon = $item->icon ? asset(Storage::url($item->icon)) : null;
            return $item;
        });

        return $newCollection;
    }

    protected function afterShow(Request $request, Model $entity)
    {
        $entity->icon = $entity->icon ? asset(Storage::url($entity->icon)) : null;
    }

    protected function afterDestroy(Request $request, Model $entity)
    {   
        if($entity->getAttribute("icon"))
        {
            unlink($_SERVER['DOCUMENT_ROOT'].Storage::url($entity->getAttribute("icon")));
        }
    }

    protected function performStore(Request $request, Model $entity, array $attributes): void
    {

        $base64_str = substr($request->icon, strpos($request->icon, ",")+1);
        $icon = base64_decode($base64_str);
        
        $fileName = time().'.'.'png';
        $success = file_put_contents(public_path().'\\storage\\icons\\'.$fileName, $icon);

        if($success) {
            $attributes['icon'] = 'public/icons/'.$fileName;
        }
        else {
            $attributes['icon'] = null;
        }

        $this->performFill($request, $entity, $attributes);
        $entity->save();
    }

    protected function performUpdate(Request $request, Model $entity, array $attributes): void
    {

        if (!filter_var($request->icon, FILTER_VALIDATE_URL)) {

            $base64_str = substr($request->icon, strpos($request->icon, ",")+1);
            $icon = base64_decode($base64_str);
            
            $fileName = time().'.'.'png';
            $success = file_put_contents(public_path().'\\storage\\icons\\'.$fileName, $icon);

            if ($entity->icon && $success) {
                unlink($_SERVER['DOCUMENT_ROOT'].Storage::url($entity->icon));
            }

            $attributes['icon'] = 'public/icons/'.$fileName;
        }
        else {
            $attributes['icon'] = $entity->icon;
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
                    'required' => 'true',
                    'sortable'=> true
                ], 
                [
                    'title' => 'Иконка',
                    'key' => 'icon',
                    'type' => 'image',
                    'required' => 'true',
                    'sortable'=> false
                ],
            ],
            'filters' => [
                'name' => [ 'value' => null, 'matchMode' => 'CONTAINS' ],
            ]
        ], Response::HTTP_OK);
    }
}
