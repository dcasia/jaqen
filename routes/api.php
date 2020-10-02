<?php

use DigitalCreative\Dashboard\Http\Controllers\ResourceController;
use Illuminate\Support\Facades\Route;

/**
 * Filters
 */
Route::get('/{resource}/filters', [ ResourceController::class, 'filters' ]);

/**
 * CRUD
 */
Route::get('/{resource}/{key}', [ ResourceController::class, 'fetch' ]);
Route::patch('/{resource}/{key}', [ ResourceController::class, 'update' ]);
Route::delete('/{resource}/{key}', [ ResourceController::class, 'delete' ]);
Route::post('/{resource}', [ ResourceController::class, 'store' ]);

Route::get('/resources', [ ResourceController::class, 'list' ]);
Route::get('/{resource}/create', [ ResourceController::class, 'create' ]);
Route::get('/{resource}', [ ResourceController::class, 'index' ]);

Route::get('/belongs-to/{resource}/{key}/{field}', [ ResourceController::class, 'searchBelongsTo' ]);
