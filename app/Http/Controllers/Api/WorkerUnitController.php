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

    public function includes(): array
    {
        return ['operationNote', 'operationNote.field', 'technic', 'technic.model', 'technic.model.type', 'equipments', 'equipments.model'];
    }

    public function filterableBy(): array
    {
        return ['is_used', 'worker_id'];
    }
}
