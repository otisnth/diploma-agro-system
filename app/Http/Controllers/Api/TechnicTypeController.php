<?php

namespace App\Http\Controllers\Api;

use App\Models\TechnicType;
use Orion\Http\Controllers\Controller;
use Orion\Concerns\DisablePagination;

class TechnicTypeController extends Controller
{
    use DisablePagination;

    protected $model = TechnicType::class;

}
