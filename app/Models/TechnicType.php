<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class TechnicType extends Model
{
    use HasFactory;

    protected $table = 'technic_types';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'name',
        'icon'
    ];

    protected $appends = ['icon_path'];

    public function getIconPathAttribute() {
        return asset(Storage::url($this->icon));
    }

}
