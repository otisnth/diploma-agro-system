<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CropRotation extends Model
{
    use HasFactory;

    protected $table = 'crop_rotations';
    protected $primaryKey = 'id';
    public $timestamps = false;


    protected $fillable = [
        'start_date',
        'end_date',
        'field_id',
        'sort_id'
    ];

    public function field()
    {
        return $this->belongsTo(Field::class, 'field_id', 'id');
    }

    public function sort()
    {
        return $this->belongsTo(Sort::class, 'sort_id', 'id');
    }
}
