<?php

namespace App\Http\Controllers\Api;

use App\Models\EquipmentWorkerUnit;
use Orion\Http\Controllers\Controller;
use Orion\Concerns\DisablePagination;

class EquipmentWorkerUnitController extends Controller
{
    use DisablePagination;

    protected $model = EquipmentWorkerUnit::class;

}
