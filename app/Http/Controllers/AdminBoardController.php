<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminBoardController extends Controller
{
    

    public function index(Request $request): Response
    {
        $references = array();

        return Inertia::render('AdminBoard/Index', [
            'references' => $references,
        ]);
    }

}
