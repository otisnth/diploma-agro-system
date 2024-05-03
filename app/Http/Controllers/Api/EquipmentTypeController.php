<?php

namespace App\Http\Controllers\Api;

use App\Models\EquipmentType;
use Orion\Http\Controllers\Controller;
use Orion\Concerns\DisablePagination;

class EquipmentTypeController extends Controller
{
    use DisablePagination;

    protected $model = EquipmentType::class;

}
