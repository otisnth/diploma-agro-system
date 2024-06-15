<?php

namespace App\Http\Controllers\Api;

use App\Models\OperationNote;
use Orion\Http\Controllers\RelationController;
use App\Policies\TruePolicy;
use Orion\Concerns\DisablePagination;



class OperationNoteWorkerUnitsController extends RelationController
{
    use DisablePagination;


    public function includes() : array
    {
        return ['worker', 'technic', 'technic.model', 'technic.model.type', 'equipments', 'equipments.model'];
    }
    
    protected $model = OperationNote::class;

    protected $policy = TruePolicy::class;

    protected $parentPolicy = TruePolicy::class;

    protected $relation = 'workerUnits';
}
