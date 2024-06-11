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
use App\Http\Controllers\Api\WorkerUnitEquipmentsController;
use App\Http\Controllers\Api\FieldController;
use App\Http\Controllers\Api\OperationNoteController;
use App\Http\Controllers\Api\SortController;
use App\Http\Controllers\Api\TechnicController;
use App\Http\Controllers\Api\WorkerUnitController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\OperationNoteWorkerUnitsController;


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
    Orion::resource('users', UserController::class);

    Route::get('plants/properties', [PlantController::class, 'properties'])->name('plants.properties');
    Orion::resource('plants', PlantController::class);

    Route::get('technic-types/properties', [TechnicTypeController::class, 'properties'])->name('technic-types.properties');
    Orion::resource('technic-types', TechnicTypeController::class);

    Route::get('equipment-types/properties', [EquipmentTypeController::class, 'properties'])->name('equipment-types.properties');
    Orion::resource('equipment-types', EquipmentTypeController::class);

    Route::get('equipment-models/properties', [EquipmentModelController::class, 'properties'])->name('equipment-models.properties');
    Orion::resource('equipment-models', EquipmentModelController::class);

    Route::get('technic-models/properties', [TechnicModelController::class, 'properties'])->name('technic-models.properties');
    Orion::resource('technic-models', TechnicModelController::class);
    
    Route::get('crop-rotations/properties', [CropRotationController::class, 'properties'])->name('crop-rotations.properties');
    Orion::resource('crop-rotations', CropRotationController::class);

    Route::get('equipments/properties', [EquipmentController::class, 'properties'])->name('equipments.properties');
    Orion::resource('equipments', EquipmentController::class);

    Route::get('fields/properties', [FieldController::class, 'properties'])->name('fields.properties');
    Route::get('fields/statuses', [FieldController::class, 'getFieldStatuses'])->name('fields.statuses');
    Orion::resource('fields', FieldController::class);

    Route::post('operation-notes/recommendations', [OperationNoteController::class, 'recommendations'])->name('operation-notes.recommendations');
    Orion::resource('operation-notes', OperationNoteController::class);

    Route::get('sorts/properties', [SortController::class, 'properties'])->name('sorts.properties');
    Orion::resource('sorts', SortController::class);

    Route::get('technics/properties', [TechnicController::class, 'properties'])->name('technics.properties');
    Route::post('technics/positions', [TechnicController::class, 'positions'])->name('technics.positions');
    Orion::resource('technics', TechnicController::class);

    Route::get('worker-units/properties', [WorkerUnitController::class, 'properties'])->name('worker-units.properties');
    Orion::resource('worker-units', WorkerUnitController::class);

    Route::get('field-statuses/properties', [FieldStatusController::class, 'properties'])->name('field-statuses.properties');
    Route::resource('field-statuses', FieldStatusController::class);

    Route::get('operation-note-statuses/properties', [OperationNoteStatusController::class, 'properties'])->name('operation-note-statuses.properties');
    Route::resource('operation-note-statuses', OperationNoteStatusController::class);

    Route::get('operations/properties', [OperationController::class, 'properties'])->name('operations.properties');
    Route::resource('operations', OperationController::class);

    Route::get('posts/properties', [PostController::class, 'properties'])->name('posts.properties');
    Route::resource('posts', PostController::class);

    Orion::hasManyResource('operation-notes', 'worker-units', OperationNoteWorkerUnitsController::class);

    Orion::belongsToManyResource('worker-units', 'equipments' , WorkerUnitEquipmentsController::class);
});