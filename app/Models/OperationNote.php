<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperationNote extends Model
{
    use HasFactory;

    protected $table = 'operation_notes';
    protected $primaryKey = 'id';

    public static $operationStatuses = array();

    public static $operations = array();

    protected $fillable = [
        'start_date',
        'end_date',
        'status',
        'operation',
        'field_id',
        'created_by'
    ];

    protected $dates = [
        'start_date',
        'end_date'
    ];

    public function field()
    {
        return $this->belongsTo(Field::class, 'field_id', 'id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by','id');
    }
}
