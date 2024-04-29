<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sort extends Model
{
    use HasFactory;

    protected $table = 'sorts';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'name',
        'temperature',
        'humidity',
        'vegetation_period',
        'plant_id'
    ];

    protected $casts = [
        'temperatur' => 'array',
        'humidity' => 'array'
    ];

    public function plant()
    {
        return $this->belongsTo(Plant::class, 'plant_id', 'id');
    }
}
