<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});



    // API routes for master data
    Route::get('/api/master-kako/{kodeBps}', [MasterKakoController::class, 'getByKodeBps']);
    Route::get('/api/master-kec/{kodeBps}', [MasterKecController::class, 'getByKodeBps']);
    Route::get('/api/master-desa/{kodeBps}', [MasterDesaController::class, 'getByKodeBps']);