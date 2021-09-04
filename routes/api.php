<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PhotographerController;

Route::get('/ping', function () {
    return ['pong' => 'Funcina, acesse para voltar apipicplus.ntnrdn1.dev.br'];
});

Route::get('/401', [AuthController::class, 'anauthorized'])->name('login');

// Route::get('/random', [PhotographerController::class, 'createRandom']);

Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/logout', [AuthController::class, 'logout']);
Route::post('/auth/refresh', [AuthController::class, 'refresh']);
Route::post('/user', [AuthController::class, 'create'])->name('create');

Route::get('/user', [UserController::class, 'read']);
Route::put('/user', [UserController::class, 'update']);
Route::post('/user/avatar', [UserController::class, 'updateAvatar']);
Route::get('/user/favorites', [UserController::class, 'getFavorites']);
Route::post('/user/favorite', [UserController::class, 'toggleFavorite']);
Route::get('/user/appointments', [UserController::class, 'getAppointments']);


Route::get('/photographers', [PhotographerController::class, 'list']);
Route::get('/photographer/{id}', [PhotographerController::class, 'one']);
Route::post('/photographer/{id}/appointment', [PhotographerController::class, 'setAppointment']);

Route::get('/search', [PhotographerController::class, 'search']);
