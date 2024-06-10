<?php

namespace App\Http\Controllers\Api;

use App\Models\OperationNote;
use Orion\Http\Controllers\RelationController;
use App\Policies\TruePolicy;


class OperationNoteWorkerUnitsController extends RelationController
{

    protected $model = OperationNote::class;

    protected $policy = TruePolicy::class;

    protected $parentPolicy = TruePolicy::class;

    protected $relation = 'workerUnits';
}
