<?php

namespace App\Http\Controllers\Api;

use App\Models\OperationNoteWorkerUnit;
use Orion\Http\Controllers\Controller;
use Orion\Concerns\DisablePagination;

class OperationNoteWorkerUnitController extends Controller
{
    use DisablePagination;

    protected $model = OperationNoteWorkerUnit::class;

}
