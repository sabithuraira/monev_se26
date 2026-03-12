<?php

use App\Http\Controllers\SubslsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// API routes for master data
Route::get('/api/master-kako/{kodeBps}', [\App\Http\Controllers\MasterKakoController::class, 'getByKodeBps']);
Route::get('/api/master-kec/{kodeBps}', [\App\Http\Controllers\MasterKecController::class, 'getByKodeBps']);
Route::get('/api/master-desa/{kodeBps}', [\App\Http\Controllers\MasterDesaController::class, 'getByKodeBps']);

// Subsls CRUD API (no store; update only allows se26_selesai, se26_diperiksa)
Route::get('/api/subsls', [SubslsController::class, 'index']);
Route::get('/api/subsls/{id}', [SubslsController::class, 'show']);
Route::match(['put', 'patch'], '/api/subsls/{id}', [SubslsController::class, 'update']);
Route::delete('/api/subsls/{id}', [SubslsController::class, 'destroy']);