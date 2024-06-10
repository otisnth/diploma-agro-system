<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    use HasFactory;

    protected $table = 'equipments';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'marking',
        'model_id'
    ];

    public function model()
    {
        return $this->belongsTo(EquipmentModel::class, 'model_id', 'id');
    }

    public function workerUnits()
    {
        return $this->belongsToMany(WorkerUnit::class, 'equipments_worker_units');
    }
}
