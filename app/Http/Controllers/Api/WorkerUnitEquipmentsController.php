<?php

namespace App\Http\Controllers\Api;

use App\Models\WorkerUnit;
use Orion\Http\Controllers\RelationController;
use App\Policies\TruePolicy;


class WorkerUnitEquipmentsController extends RelationController
{

    protected $model = WorkerUnit::class;

    protected $policy = TruePolicy::class;

    protected $parentPolicy = TruePolicy::class;

    protected $relation = 'equipments';
}
