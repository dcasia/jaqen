<?php

use DigitalCreative\Dashboard\Http\Controllers\ResourceController;
use Illuminate\Support\Facades\Route;

Route::get('/{resource}', [ ResourceController::class, 'index' ]);
Route::get('/{resource}/filters', [ ResourceController::class, 'filters' ]);
Route::post('/create/{resource}', [ ResourceController::class, 'create' ]);
Route::post('/{resource}/{key}', [ ResourceController::class, 'update' ]);
Route::get('/{resource}/{key}', [ ResourceController::class, 'fetch' ]);

Route::get('/belongs-to/{resource}/{key}/{field}', [ ResourceController::class, 'searchBelongsTo' ]);
