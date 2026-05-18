<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MasterDesaController;
use App\Http\Controllers\MasterKakoController;
use App\Http\Controllers\MasterKecController;
use App\Http\Controllers\SubslsController;
use Illuminate\Support\Facades\Route;

// Public
Route::post('/login', [AuthController::class, 'login']);

Route::get('master-kako', [MasterKakoController::class, 'index']);
Route::get('master-kec', [MasterKakoController::class, 'index']);
Route::get('master-desa', [MasterDesaController::class, 'index']);
Route::get('histogram_kecamatan', [DashboardController::class, "histogram_kecamatan"]);
Route::get('histogram_desa', [DashboardController::class, "histogram_desa"]);
// Protected by Sanctum
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // API resources (index, store, show, update, destroy)
    Route::apiResource('master-kako', MasterKakoController::class)->except(['index'])->parameters(['master_kako' => 'id']);
    Route::apiResource('master-kec', MasterKecController::class)->except(['index'])->parameters(['master_kec' => 'id']);
    Route::apiResource('master-desa', MasterDesaController::class)->except(['index'])->parameters(['master_desa' => 'id']);
    Route::apiResource('subsls', SubslsController::class)->parameters(['subsls' => 'id']);


});
