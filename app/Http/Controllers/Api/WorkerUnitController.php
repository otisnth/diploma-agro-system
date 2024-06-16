<?php

namespace App\Http\Controllers\Api;

use App\Models\WorkerUnit;
use Orion\Http\Controllers\Controller;
use Orion\Concerns\DisablePagination;
use App\Policies\TruePolicy;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

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
        return ['is_used', 'worker_id', 'operation_note_id'];
    }

    protected function beforeUpdate(Request $request, Model $entity)
    {

        if ($request->complete_confirm && !$entity->complete_confirm && !$entity->end_date) {
            $entity->end_date = date('Y-m-d H:i:s');
        }

        // if ($request->is_used && !$entity->is_used && !$entity->start_date) {
        //     $entity->start_date = date('Y-m-d H:i:s');
        // }
    }

    protected function afterUpdate(Request $request, Model $entity)
    {

        if ($request->complete_confirm) {
            $operationNote = $entity->operationNote;

            $workersUnits = $operationNote->workerUnits;

            $completeWorkerUnits = $workersUnits->where('complete_confirm', true);

            if ($workersUnits->count() == $completeWorkerUnits->count()) {
                $operationNote->update(['status' => 'awaitConfirm']);
            }
        }
    }
}
