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


    public function includes(): array
    {
        return ['worker', 'technic', 'technic.model', 'technic.model.type', 'equipments', 'equipments.model'];
    }

    protected function beforeDestroy(Request $request, Model $parentEntity, Model $entity)
    {
        if ($entity->is_used) {
            return response()->json(['error' => 'Данный исполнитель уже начал выполнение мероприятия'], 422);
        }
    }

    protected function beforeStore(Request $request, Model $parentEntity, Model $entity)
    {
        $technic_id = $request->technic_id;
        $worker_id = $request->worker_id;
        $equipments_ids = $request->equipments;

        $workerUnits = $parentEntity->workerUnits->where('worker_id', $worker_id);

        if ($workerUnits->count() > 0) {
            return response()->json(['error' => 'Данный механизатор уже задействован в этом мероприятии'], 422);
        }

        $workerUnits = $parentEntity->workerUnits->where('technic_id', $technic_id);

        if ($workerUnits->count() > 0) {
            return response()->json(['error' => 'Данная техника уже задействована в этом мероприятии'], 422);
        }

        if ($equipments_ids) {
            $workerUnits = $parentEntity->workerUnits;

            $allEquipmentIds  = $workerUnits->pluck('equipments')
                ->flatten()
                ->pluck('id');

            foreach ($equipments_ids as $equipmentId) {

                if ($allEquipmentIds->contains($equipmentId)) {
                    return response()->json(['error' => 'Выбранное оборудование уже задействовано в этом мероприятии'], 422);
                }
            }
        }
    }

    protected $model = OperationNote::class;

    protected $policy = TruePolicy::class;

    protected $parentPolicy = TruePolicy::class;

    protected $relation = 'workerUnits';
}
