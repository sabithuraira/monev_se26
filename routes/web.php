<?php

use App\Http\Controllers\SubslsController;
use App\Http\Controllers\WebPageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WebPageController::class, 'dashboard']);
Route::get('/dashboard', [WebPageController::class, 'dashboard']);
Route::get('/data-subsls', [SubslsController::class, 'indexPage']);
Route::get('/subsls/data', [SubslsController::class, 'listData']);
Route::get('/subsls/options/kabupaten', [SubslsController::class, 'kabupatenOptions']);
Route::get('/subsls/options/kecamatan', [SubslsController::class, 'kecamatanOptions']);
Route::get('/subsls/options/desa', [SubslsController::class, 'desaOptions']);
Route::get('/subsls/rekap-data', [SubslsController::class, 'rekap']);
Route::get('/rekapitulasi', [WebPageController::class, 'rekapitulasi']);
Route::get('/hasil-klaster', [WebPageController::class, 'hasilKlaster']);
