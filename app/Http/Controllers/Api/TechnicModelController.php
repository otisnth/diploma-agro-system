<?php

namespace App\Http\Controllers\Api;

use App\Models\TechnicModel;
use Orion\Http\Controllers\Controller;
use Orion\Concerns\DisablePagination;


class TechnicModelController extends Controller
{
    use DisablePagination;
    protected $model = TechnicModel::class;

}
