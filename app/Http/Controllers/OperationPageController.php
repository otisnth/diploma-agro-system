<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\OperationNote;
use App\Models\Field;

class OperationPageController extends Controller
{


    public function index(Request $request): Response
    {
        $operations = OperationNote::$operations;
        $operationStatuses = OperationNote::$operationStatuses;

        return Inertia::render('Operation/Index', [
            'operations' => $operations,
            'operationStatuses' => $operationStatuses
        ]);
    }

    public function create(Request $request): Response
    {
        $operations = OperationNote::$operations;

        return Inertia::render('Operation/Create', [
            'operations' => $operations,
        ]);
    }

    public function detail(string $id): Response
    {
        $operations = OperationNote::$operations;
        $operationStatuses = OperationNote::$operationStatuses;
        $statuses = Field::$fieldStatuses;

        if (Auth()->user()->post == 'worker') {
            return Inertia::render('Worker/Operation', [
                'id' => $id,
                'fieldStatuses' => $statuses,
                'operations' => $operations,
                'operationStatuses' => $operationStatuses
            ]);
        }


        return Inertia::render('Operation/Detail', [
            'id' => $id,
            'fieldStatuses' => $statuses,
            'operations' => $operations,
            'operationStatuses' => $operationStatuses
        ]);
    }
}
