<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Field;

class FieldPageController extends Controller
{
    

    public function index(Request $request): Response
    {

        return Inertia::render('Field/Index', [
        ]);
    }

    public function create(Request $request): Response
    {
        $statuses = Field::$fieldStatuses;

        return Inertia::render('Field/Create', [
            'fieldStatuses' => $statuses,
        ]);
    }

    public function detail(string $id): Response
    {
        $statuses = Field::$fieldStatuses;

        return Inertia::render('Field/Detail', [
            'id' => $id,
            'fieldStatuses' => $statuses,
        ]);
    }

}
