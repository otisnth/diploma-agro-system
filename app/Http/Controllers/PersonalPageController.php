<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PersonalPageController extends Controller
{
    public function index(Request $request): Response
    {
        $posts = User::$posts;

        $posts = array_reduce($posts, function($carry, $item) {
            if ($item['id'] !== 'owner') {
                $carry[] = $item;
            }
            return $carry;
        }, []);

        return Inertia::render('Personal/Index', [
            'posts' => $posts,
        ]);
    }

    public function create(Request $request): Response
    {

        $posts = User::$posts;

        $posts = array_reduce($posts, function($carry, $item) {
            if ($item['id'] !== 'owner') {
                $carry[] = $item;
            }
            return $carry;
        }, []);

        return Inertia::render('Personal/Create', [
            'posts' => $posts,
        ]);
    }

}
