<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperationNoteWorkerUnit extends Model
{
    use HasFactory;

    protected $table = 'operation_notes_worker_units';
    protected $primaryKey = ['operation_note_id', 'worker_unit_id'];
    public $incrementing = false;
    public $timestamps = false;


    protected $fillable = [
        'operation_note_id',
        'worker_unit_id',
    ];

    public function operationNote()
    {
        return $this->belongsTo(OperationNote::class, 'operation_note_id', 'id');
    }

    public function workerUnit()
    {
        return $this->belongsTo(WorkerUnit::class, 'worker_unit_id', 'id');
    }
}
