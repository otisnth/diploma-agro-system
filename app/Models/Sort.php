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
        'plant_id',
        'temperature_weight',
        'humidity_weight'
    ];

    public function plant()
    {
        return $this->belongsTo(Plant::class);
    }
}
