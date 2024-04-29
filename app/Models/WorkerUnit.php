<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkerUnit extends Model
{
    use HasFactory;

    protected $table = 'worker_units';
    protected $primaryKey = 'id';

    protected $fillable = [
        'is_used',
        'worker_id',
        'technic_id',
    ];

    protected $casts = [
        'is_used' => 'boolean',
    ];

    public function worker()
    {
        return $this->belongsTo(User::class, 'worker_id', 'id');
    }

    public function technic()
    {
        return $this->belongsTo(Technic::class, 'technic_id', 'id');
    }
}
