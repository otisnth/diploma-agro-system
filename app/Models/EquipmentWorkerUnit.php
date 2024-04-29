<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EquipmentWorkerUnit extends Model
{
    use HasFactory;

    protected $table = 'equipments_worker_units';

    protected $primaryKey = ['equipment_id', 'worker_unit_id'];

    public $incrementing = false;
    public $timestamps = false;


    protected $fillable = [
        'equipment_id',
        'worker_unit_id',
    ];

    public function equipment()
    {
        return $this->belongsTo(Equipment::class, 'equipment_id', 'id');
    }

    public function workerUnit()
    {
        return $this->belongsTo(WorkerUnit::class, 'worker_unit_id', 'id');
    }
}
