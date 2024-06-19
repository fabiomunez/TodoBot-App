<?php

use App\Http\Controllers\BotManController;
use App\Models\Todo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Route::get('/', function () {
    //     Todo::create(['task' => 'Test Task']);
    //     return 'Todo item created';
    // });

    Auth::routes();

    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home')->middleware('auth');
    Route::match(['get', 'post'], '/botman', [BotManController::class, 'handle'])->middleware('auth');

    Route::post('/complete-todo/{id}', [BotManController::class, 'completeTodo'])->name('index')->middleware('auth');
