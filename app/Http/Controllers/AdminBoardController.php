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
        $references = array(
            [
                "id" => "plants",
                "name"=> "Культуры",
                "editable" => true,
                "expandable"=> true,
            ],
            [
                "id" => "sorts",
                "name"=> "Сорта",
                "editable" => true,
                "expandable"=> true,
            ],
            [
                "id" => "field-statuses",
                "name"=> "Состояния поля",
                "editable" => false,
                "expandable"=> false,
            ],
            [
                "id" => "operation-note-statuses",
                "name"=> "Статус мероприятия",
                "editable" => false,
                "expandable"=> false,
            ],
            [
                "id" => "operations",
                "name"=> "Мероприятия",
                "editable" => false,
                "expandable"=> false,
            ],
            [
                "id" => "posts",
                "name"=> "Должности",
                "editable" => false,
                "expandable"=> false,
            ],
            [
                "id" => "equipment-types",
                "name"=> "Типы оборудования",
                "editable" => true,
                "expandable"=> true,
            ],
            [
                "id" => "equipment-models",
                "name"=> "Модели оборудования",
                "editable" => true,
                "expandable"=> true,
            ],
            [
                "id" => "technic-types",
                "name"=> "Типы техники",
                "editable" => true,
                "expandable"=> true,
            ],
            [
                "id" => "technic-models",
                "name"=> "Модели техники",
                "editable" => true,
                "expandable"=> true,
            ],
        );

        return Inertia::render('AdminBoard/Index', [
            'references' => $references,
        ]);
    }

}
