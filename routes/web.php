<?php

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the 'web' middleware group. Now create something great!
|
 */


Route::get("/sso/login", [LoginController::class, 'getLogin'])->name("sso.login");
Route::get("/callback", [LoginController::class, 'getCallback'])->name("callback");
Route::get("/connect", [LoginController::class, 'connectUser'])->name("connect");

Auth::routes(['register' => false, 'reset' => false]);

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('index');
