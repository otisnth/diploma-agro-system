<?php

namespace App\Http\Controllers;

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
                "name"=> "Культура",
                "editable" => true,
                "expandable"=> true,
            ],
            [
                "id" => "sorts",
                "name"=> "Сорт",
                "editable" => true,
                "expandable"=> true,
            ],
            [
                "id" => "field-statuses",
                "name"=> "Состояние поля",
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
                "name"=> "Мероприятие",
                "editable" => false,
                "expandable"=> false,
            ],
            [
                "id" => "posts",
                "name"=> "Должность",
                "editable" => false,
                "expandable"=> false,
            ],
            [
                "id" => "equipment-types",
                "name"=> "Тип оборудования",
                "editable" => true,
                "expandable"=> true,
            ],
            [
                "id" => "equipment-models",
                "name"=> "Модель оборудования",
                "editable" => true,
                "expandable"=> true,
            ],
            [
                "id" => "equipments",
                "name"=> "Оборудование",
                "editable" => true,
                "expandable"=> true,
            ],
            [
                "id" => "technic-types",
                "name"=> "Тип техники",
                "editable" => true,
                "expandable"=> true,
            ],
            [
                "id" => "technic-models",
                "name"=> "Модель техники",
                "editable" => true,
                "expandable"=> true,
            ],
            [
                "id" => "technics",
                "name"=> "Техника",
                "editable" => true,
                "expandable"=> true,
            ],
        );

        return Inertia::render('AdminBoard/Index', [
            'references' => $references,
        ]);
    }

}
