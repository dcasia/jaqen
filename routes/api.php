<?php

use DigitalCreative\Dashboard\Http\Controllers\ResourceController;
use Illuminate\Support\Facades\Route;

Route::get('/resources', [ ResourceController::class, 'list' ]);
Route::get('/{resource}/filters', [ ResourceController::class, 'filters' ]);
Route::post('/create/{resource}', [ ResourceController::class, 'store' ]);
Route::get('/{resource}/create', [ ResourceController::class, 'create' ]);
Route::post('/{resource}/{key}', [ ResourceController::class, 'update' ]);
Route::get('/{resource}/{key}', [ ResourceController::class, 'fetch' ]);
Route::get('/{resource}', [ ResourceController::class, 'index' ]);

Route::get('/belongs-to/{resource}/{key}/{field}', [ ResourceController::class, 'searchBelongsTo' ]);
