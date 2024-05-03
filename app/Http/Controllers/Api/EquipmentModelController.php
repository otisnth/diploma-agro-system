<?php

namespace App\Http\Controllers\Api;

use App\Models\EquipmentModel;
use Orion\Http\Controllers\Controller;
use Orion\Concerns\DisablePagination;

class EquipmentModelController extends Controller
{
    use DisablePagination;

    protected $model = EquipmentModel::class;

}
