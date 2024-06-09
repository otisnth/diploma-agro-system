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
            'id' => 0,
            'name' => 'В работе',
        ],
        [
            'id' => 1,
            'name' => 'Завершено',
        ]
    );

    public static $operations = array(
        [
            'id' => 'seeding',
            'name' => 'Посев',
        ],
        [
            'id' => 'cultivation',
            'name' => 'Культивация',
        ],
        [
            'id' => 'spraying',
            'name' => 'Опрыскивание растений',
        ],
        [
            'id' => 'fertilization',
            'name' => 'Удобрение',
        ],
        [
            'id' => 'harvest',
            'name' => 'Уборка урожая',
        ]
    );

    protected $fillable = [
        'start_date',
        'end_date',
        'status',
        'operation',
        'field_id',
        'created_by'
    ];

    protected $dates = [
        'start_date',
        'end_date'
    ];

    public function field()
    {
        return $this->belongsTo(Field::class, 'field_id', 'id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by','id');
    }

    public function workerUnits()
    {
        return $this->belongsToMany(WorkerUnit::class, 'operation_notes_worker_units', 'operation_note_id', 'worker_unit_id');
    }
}
