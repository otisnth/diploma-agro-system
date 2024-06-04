<?php

namespace App\Http\Controllers\Api;

use App\Models\EquipmentWorkerUnit;
use Orion\Http\Controllers\Controller;
use Orion\Concerns\DisablePagination;
use App\Policies\TruePolicy;

class EquipmentWorkerUnitController extends Controller
{
    use DisablePagination;

    protected $policy = TruePolicy::class;

    protected $model = EquipmentWorkerUnit::class;

}
