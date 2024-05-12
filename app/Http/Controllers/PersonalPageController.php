<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PersonalPageController extends Controller
{
    

    public function index(Request $request): Response
    {

        return Inertia::render('Personal/Index', [
        ]);
    }

    public function create(Request $request): Response
    {

        return Inertia::render('Personal/Create', [
        ]);
    }

}
