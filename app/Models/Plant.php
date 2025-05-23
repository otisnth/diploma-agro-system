<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plant extends Model
{
    use HasFactory;

    protected $table = 'plants';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'name',
        'color',
        'weather_rules'
    ];

    public function sorts() {
        return $this->hasMany(Sort::class);
    }

    protected $casts = [
        'weather_rules' => 'array',
    ];
}
