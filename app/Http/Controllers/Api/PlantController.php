<?php

namespace App\Http\Controllers\Api;

use App\Models\Plant;
use App\Policies\DefaultPolicy;
use Orion\Http\Controllers\Controller;

class PlantController extends Controller
{
    protected $model = Plant::class;

}
