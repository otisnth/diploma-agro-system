<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\OperationNote;

class OperationPageController extends Controller
{
    

    public function index(Request $request): Response
    {

        return Inertia::render('Operation/Index', [
        ]);
    }

    public function create(Request $request): Response
    {
        $operations = OperationNote::$operations;

        return Inertia::render('Operation/Create', [
            'operations' => $operations,
        ]);
    }

}
