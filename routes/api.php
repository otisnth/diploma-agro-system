<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Orion\Facades\Orion;

use App\Http\Controllers\Api\PlantController;
use App\Http\Controllers\Api\TechnicTypeController;
use App\Http\Controllers\Api\EquipmentTypeController;
use App\Http\Controllers\Api\EquipmentModelController;
use App\Http\Controllers\Api\TechnicModelController;
use App\Http\Controllers\Api\CropRotationController;
use App\Http\Controllers\Api\EquipmentController;
use App\Http\Controllers\Api\EquipmentWorkerUnitController;
use App\Http\Controllers\Api\FieldController;
use App\Http\Controllers\Api\OperationNoteController;
use App\Http\Controllers\Api\OperationNoteWorkerUnitController;
use App\Http\Controllers\Api\SortController;
use App\Http\Controllers\Api\TechnicController;
use App\Http\Controllers\Api\WorkerUnitController;

use App\Http\Controllers\Api\FieldStatusController;
use App\Http\Controllers\Api\OperationNoteStatusController;
use App\Http\Controllers\Api\OperationController;
use App\Http\Controllers\Api\PostController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['as' => 'api.'], function() {
    Orion::resource('plants', PlantController::class);
    Orion::resource('technic-types', TechnicTypeController::class);
    Orion::resource('equipment-types', EquipmentTypeController::class);
    Orion::resource('equipment-models', EquipmentModelController::class);
    Orion::resource('technic-models', TechnicModelController::class);
    Orion::resource('crop-rotations', CropRotationController::class);
    Orion::resource('equipments', EquipmentController::class);
    Orion::resource('equipments-worker-units', EquipmentWorkerUnitController::class);
    Orion::resource('fields', FieldController::class);
    Orion::resource('operation-notes', OperationNoteController::class);
    Orion::resource('operation-notes-worker-units', OperationNoteWorkerUnitController::class);
    Orion::resource('sorts', SortController::class);
    Orion::resource('technics', TechnicController::class);
    Orion::resource('worker-units', WorkerUnitController::class);

    Route::resource('field-statuses', FieldStatusController::class);
    Route::resource('operation-note-statuses', OperationNoteStatusController::class);
    Route::resource('operations', OperationController::class);
    Route::resource('posts', PostController::class);

});