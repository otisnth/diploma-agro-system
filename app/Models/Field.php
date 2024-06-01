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
            'id' => 'stubbleTillage',
            'name' => 'Культивация стерни',
            'color' => '78b795',
        ],
        [
            'id' => 'cultivated',
            'name' => 'Культивировано',
            'color' => '56a3db',
        ],
        [
            'id' => 'plowed',
            'name' => 'Вспахано',
            'color' => '52414e',
        ],
        [
            'id' => 'seedbed',
            'name' => 'Засеяно',
            'color' => '50d3ac',
        ],
        [
            'id' => 'growing',
            'name' => 'Растет',
            'color' => '82c722',
        ],
        [
            'id' => 'readyToHarvest',
            'name' => 'Урожай созрел',
            'color' => 'e3941c',
        ],
        [
            'id' => 'harvested',
            'name' => 'Урожай убран',
            'color' => '8a599f',
        ],
        [
            'id' => 'removeFoliage',
            'name' => 'Можно косить',
            'color' => 'd93a1a',
        ],
        [
            'id' => 'withered',
            'name' => 'Урожай погиб',
            'color' => '693c1a',
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
