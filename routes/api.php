<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MasterDesaController;
use App\Http\Controllers\MasterKakoController;
use App\Http\Controllers\MasterKecController;
use App\Http\Controllers\SubslsController;
use Illuminate\Support\Facades\Route;

// Public
Route::post('/login', [AuthController::class, 'login']);

// Protected by Sanctum
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // API resources (index, store, show, update, destroy)
    Route::apiResource('master-kako', MasterKakoController::class)->parameters(['master_kako' => 'id']);
    Route::apiResource('master-kec', MasterKecController::class)->parameters(['master_kec' => 'id']);
    Route::apiResource('master-desa', MasterDesaController::class)->parameters(['master_desa' => 'id']);
    Route::apiResource('subsls', SubslsController::class)->parameters(['subsls' => 'id']);
});
