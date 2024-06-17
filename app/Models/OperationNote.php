<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperationNote extends Model
{
    use HasFactory;

    protected $table = 'operation_notes';
    protected $primaryKey = 'id';

    public static $operationStatuses = array(
        [
            'id' => 'planned',
            'name' => 'Запланировано',
        ],
        [
            'id' => 'assigned',
            'name' => 'Назначено',
        ],
        [
            'id' => 'inProgress',
            'name' => 'Выполняется',
        ],
        [
            'id' => 'awaitConfirm',
            'name' => 'Ожидает подтверждения выполнения',
        ],
        [
            'id' => 'completed',
            'name' => 'Завершено',
        ],
        [
            'id' => 'canceled',
            'name' => 'Отменено',
        ]
    );

    public static $operations = array(
        [
            'id' => 'seeding',
            'currentFieldStatus' => ['cultivated', 'stubbleTillage', 'withered', 'plowed'],
            'nextFieldStatus' => ['seedbed'],
            'isNeedField' => true,
            'name' => 'Посев',
            'efficiencyCalculate' => true,
        ],
        [
            'id' => 'mowingTops',
            'currentFieldStatus' => ['removeFoliage'],
            'nextFieldStatus' => ['withered', 'readyToHarvest'],
            'isNeedField' => true,
            'name' => 'Покос ботвы',
            'efficiencyCalculate' => true,
        ],
        [
            'id' => 'plowing',
            'currentFieldStatus' => ['cultivated', 'stubbleTillage', 'withered', 'harvested'],
            'nextFieldStatus' => ['plowed'],
            'isNeedField' => true,
            'name' => 'Вспашка',
            'efficiencyCalculate' => true,
        ],
        [
            'id' => 'cultivation',
            'currentFieldStatus' => ['stubbleTillage', 'withered', 'plowed'],
            'nextFieldStatus' => ['cultivated'],
            'isNeedField' => true,
            'name' => 'Культивация',
            'efficiencyCalculate' => true,
        ],
        [
            'id' => 'spraying',
            'currentFieldStatus' => ['growing'],
            'isNeedField' => true,
            'name' => 'Опрыскивание растений',
            'efficiencyCalculate' => true,
        ],
        [
            'id' => 'fertilization',
            'isNeedField' => true,
            'name' => 'Удобрение',
            'efficiencyCalculate' => true,
        ],
        [
            'id' => 'harvest',
            'currentFieldStatus' => ['readyToHarvest', 'removeFoliage'],
            'nextFieldStatus' => ['harvested'],
            'isNeedField' => true,
            'name' => 'Уборка урожая',
            'efficiencyCalculate' => true,
        ],
        [
            'id' => 'unloadingField',
            'currentFieldStatus' => ['readyToHarvest'],
            'isNeedField' => true,
            'name' => 'Разгрузка комбайна',
            'efficiencyCalculate' => false,
        ],
        [
            'id' => 'transportation',
            'isNeedField' => false,
            'name' => 'Перевозка продукции',
            'efficiencyCalculate' => false,
        ]
    );

    protected $fillable = [
        'start_date',
        'end_date',
        'status',
        'operation',
        'field_id',
        'created_by',
        'sort_id'
    ];

    protected $dates = [
        'start_date',
        'end_date'
    ];

    protected $appends = ['note_status', 'note_operation'];

    public function getNoteStatusAttribute()
    {
        foreach (self::$operationStatuses as $status) {
            if ($status['id'] === $this->status) {
                return $status;
            }
        }
    }

    public function getNoteOperationAttribute()
    {
        foreach (self::$operations as $operation) {
            if ($operation['id'] === $this->operation) {
                return $operation;
            }
        }
    }

    public function sort()
    {
        return $this->belongsTo(Sort::class, 'sort_id', 'id');
    }
    public function field()
    {
        return $this->belongsTo(Field::class, 'field_id', 'id');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function workerUnits()
    {
        return $this->hasMany(WorkerUnit::class, 'operation_note_id', 'id');
    }
}
