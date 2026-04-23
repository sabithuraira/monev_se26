<?php

use App\Http\Controllers\SubslsController;
use Illuminate\Support\Facades\Route;

Route::get('/', [SubslsController::class, 'indexPage']);
Route::get('/subsls/data', [SubslsController::class, 'listData']);
Route::get('/subsls/options/kabupaten', [SubslsController::class, 'kabupatenOptions']);
Route::get('/subsls/options/kecamatan', [SubslsController::class, 'kecamatanOptions']);
Route::get('/subsls/options/desa', [SubslsController::class, 'desaOptions']);
