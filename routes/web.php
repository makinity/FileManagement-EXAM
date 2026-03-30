<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\FileManagementController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
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

Route::get('/', [LoginController::class, 'index'])->name('login.index');
Route::get('/forgot-password', [LoginController::class, 'forgot'])->name('forgot.index');
Route::post('/login', [LoginController::class, 'login'])->name('login');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/register', [RegisterController::class, 'index'])->name('register.index');
Route::post('/register', [RegisterController::class, 'register'])->name('register');

Route::prefix('admin')->group(function(){
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard.index');

    Route::get('/files', [FileManagementController::class, 'index'])->name('file.index');
    Route::put('/files/{file}', [FileManagementController::class, 'update'])->name('file.update');
    Route::delete('/files/{file}', [FileManagementController::class, 'destroy'])->name('file.destroy');
});
