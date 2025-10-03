<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Message API routes
Route::prefix('messages')->group(function () {
    Route::get('/', [App\Http\Controllers\MessageController::class, 'index']);
    Route::post('/', [App\Http\Controllers\MessageController::class, 'store']);
});
