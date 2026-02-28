<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ImageController;
use Illuminate\Support\Facades\Route;


Route::prefix('/user')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:api')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh-token', [AuthController::class, 'refresh']);
    });
});

Route::middleware('auth:api')->group(function () {
    Route::prefix('/image')->group(function () {
        Route::get('/', [ImageController::class, 'index']);
        Route::get('/{id}', [ImageController::class, 'get']);
        Route::post('/upload', [ImageController::class, 'upload']);
        Route::delete('/delete', [ImageController::class, 'delete']);
    });
});
