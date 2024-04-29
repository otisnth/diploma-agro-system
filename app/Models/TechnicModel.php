<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TechnicModel extends Model
{
    use HasFactory;

    protected $table = 'technic_models';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'name',
        'type_id'
    ];

    public function type()
    {
        return $this->belongsTo(TechnicType::class, 'type_id', 'id');
    }
}
