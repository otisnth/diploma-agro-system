<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Http\Request;

use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\PasswordController;

use App\Http\Controllers\MainPageController;
use App\Http\Controllers\AdminBoardController;
use App\Http\Controllers\FieldPageController;
use App\Http\Controllers\OperationPageController;
use App\Http\Controllers\PersonalPageController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [MainPageController::class, 'welcome'])->name('welcome');

Route::get('/main', [MainPageController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/report', [MainPageController::class, 'report'])->middleware(['auth', 'verified'])->name('report.index');

Route::get('/operation/{id}', [OperationPageController::class, 'detail'])
    ->where('id', '[0-9]+')
    ->name('operation.detail');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware('admin')->group(function () {
        Route::get('/adminboard', [AdminBoardController::class, 'index'])->name('adminboard');

        Route::get('/field', [FieldPageController::class, 'index'])->name('field.index');
        Route::get('/field/create', [FieldPageController::class, 'create'])->name('field.create');
        Route::get('/field/{id}', [FieldPageController::class, 'detail'])
            ->where('id', '[0-9]+')
            ->name('field.detail');


        Route::get('/operation', [OperationPageController::class, 'index'])->name('operation.index');
        Route::get('/operation/create', [OperationPageController::class, 'create'])->name('operation.create');


        Route::get('/personal', [PersonalPageController::class, 'index'])->name('personal.index');
        Route::get('/personal/create', [PersonalPageController::class, 'create'])->name('personal.create');
        Route::get('/personal/{id}', [PersonalPageController::class, 'detail'])
            ->where('id', '[0-9]+')
            ->name('personal.detail');

        Route::post('add-personal', [RegisteredUserController::class, 'addPersonal'])->name('addPersonal');
        Route::patch('/updatePersonal', [ProfileController::class, 'updatePersonal'])->name('updatePersonal');
        Route::put('updatePassword', [PasswordController::class, 'updatePassword'])->name('updatePassword');
    });
});


// Для тестирования
Route::get('/tokens/create', function (Request $request) {
    $token = $request->user()->createToken('test');


    return ['token' => $token->plainTextToken];
});

require __DIR__ . '/auth.php';
