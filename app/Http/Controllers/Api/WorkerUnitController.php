<?php

namespace App\Http\Controllers\Api;

use App\Models\WorkerUnit;
use Orion\Http\Controllers\Controller;
use Orion\Concerns\DisablePagination;
use App\Policies\TruePolicy;

class WorkerUnitController extends Controller
{
    use DisablePagination;

    protected $model = WorkerUnit::class;

    protected $policy = TruePolicy::class;

}
