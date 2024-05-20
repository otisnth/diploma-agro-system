<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Orion\Http\Controllers\Controller;

class UserController extends Controller
{
    protected $model = User::class;

    public function filterableBy(): array
    {
        return ['post'];
    }

    public function sortableBy(): array
    {
        return ['name'];
    }

}
