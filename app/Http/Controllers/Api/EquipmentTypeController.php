<?php

namespace App\Http\Controllers\Api;

use App\Models\EquipmentType;
use Orion\Http\Controllers\Controller;
use Orion\Concerns\DisablePagination;
use Symfony\Component\HttpFoundation\Response;
use Orion\Http\Requests\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Policies\TruePolicy;

class EquipmentTypeController extends Controller
{
    use DisablePagination;

    protected $model = EquipmentType::class;

    protected $policy = TruePolicy::class;


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
            ],
            'filters' => [
                'name' => [ 'value' => null, 'matchMode' => 'CONTAINS' ],
            ]
        ], Response::HTTP_OK);
    }
}
