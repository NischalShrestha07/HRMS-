<?php

use App\Http\Controllers\Api\ApiLoginController;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');
Route::post('login', [ApiLoginController::class, 'authenticate']);
Route::post('register', [ApiLoginController::class, 'register']);
Route::post('logout', [ApiLoginController::class, 'logout']);
