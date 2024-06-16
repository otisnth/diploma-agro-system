<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkerUnit extends Model
{
    use HasFactory;

    protected $table = 'worker_units';
    protected $primaryKey = 'id';

    protected $fillable = [
        'is_used',
        'complete_confirm',
        'operation_note_id',
        'worker_id',
        'technic_id',
        'report',
    ];

    protected $casts = [
        'is_used' => 'boolean',
    ];

    public function worker()
    {
        return $this->belongsTo(User::class, 'worker_id', 'id');
    }

    public function technic()
    {
        return $this->belongsTo(Technic::class, 'technic_id', 'id');
    }

    public function operationNote()
    {
        return $this->belongsTo(OperationNote::class, 'operation_note_id', 'id');
    }

    public function equipments()
    {
        return $this->belongsToMany(Equipment::class, 'equipments_worker_units');
    }
}
