<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Technic extends Model
{
    use HasFactory;

    protected $table = 'technics';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'license_plate',
        'tr_device_id',
        'model_id'
    ];

    public function model()
    {
        return $this->belongsTo(TechnicModel::class, 'model_id', 'id');
    }

    public function workerUnits()
    {
        return $this->hasMany(WorkerUnit::class, 'technic_id', 'id');
    }
}
