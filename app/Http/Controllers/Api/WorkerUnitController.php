<?php

namespace App\Http\Controllers\Api;

use App\Models\WorkerUnit;
use Orion\Http\Controllers\Controller;
use Orion\Concerns\DisablePagination;

class WorkerUnitController extends Controller
{
    use DisablePagination;

    protected $model = WorkerUnit::class;

}
