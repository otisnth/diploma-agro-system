<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Field extends Model
{
    use HasFactory;

    protected $table = 'fields';
    protected $primaryKey = 'id';

    public static $fieldStatuses = array(
        [
            'name' => 'Нуждается во вспашке',
        ],
        [
            'name' => 'Идет уборка',
        ]
    );

    protected $fillable = [
        'name',
        'coords',
        'status',
        'square',
        'tr_geofence_id',
        'sort_id'
    ];

    protected $casts = [
        'coords' => 'array'
    ];

    public function sort()
    {
        return $this->belongsTo(Sort::class, 'sort_id', 'id');
    }

    public function cropHistory(){
        return $this->hasMany(CropRotation::class, 'field_id','id');
    }
}
