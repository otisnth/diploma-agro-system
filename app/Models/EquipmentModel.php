<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EquipmentModel extends Model
{
    use HasFactory;

    protected $table = 'equipment_models';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'name',
        'work_speed',
        'work_width',
        'type_id'
    ];

    public function type()
    {
        return $this->belongsTo(EquipmentType::class, 'type_id','id');
    }
}
