<?php

namespace App\Http\Controllers\Api;

use App\Models\OperationNote;
use Orion\Http\Controllers\Controller;
use App\Policies\TruePolicy;

class OperationNoteController extends Controller
{

    protected $model = OperationNote::class;

    protected $policy = TruePolicy::class;

}
