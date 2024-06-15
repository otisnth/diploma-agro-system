<?php

namespace App\Http\Controllers\Api;

use App\Models\OperationNote;
use Orion\Http\Controllers\RelationController;
use App\Policies\TruePolicy;
use Orion\Concerns\DisablePagination;
use Illuminate\Database\Eloquent\Model;
use Orion\Http\Requests\Request;


class OperationNoteWorkerUnitsController extends RelationController
{
    use DisablePagination;


    public function includes() : array
    {
        return ['worker', 'technic', 'technic.model', 'technic.model.type', 'equipments', 'equipments.model'];
    }

    protected function beforeDestroy(Request $request, Model $parentEntity, Model $entity)
    {
        if ($entity->is_used) {
            return response()->json(['error' => 'Данный исполнитель уже начал выполнение мероприятия'], 422);
        }
    }

    protected $model = OperationNote::class;

    protected $policy = TruePolicy::class;

    protected $parentPolicy = TruePolicy::class;

    protected $relation = 'workerUnits';
}
