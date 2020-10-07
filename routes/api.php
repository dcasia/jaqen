<?php

use DigitalCreative\Dashboard\Http\Controllers\DeleteController;
use DigitalCreative\Dashboard\Http\Controllers\DetailController;
use DigitalCreative\Dashboard\Http\Controllers\IndexController;
use DigitalCreative\Dashboard\Http\Controllers\Relationships\BelongsToController;
use DigitalCreative\Dashboard\Http\Controllers\ResourceController;
use DigitalCreative\Dashboard\Http\Controllers\StoreController;
use DigitalCreative\Dashboard\Http\Controllers\UpdateController;
use Illuminate\Support\Facades\Route;

/**
 * Filters
 */
Route::get('/{resource}/filters', [ ResourceController::class, 'filters' ]);
Route::get('/{resource}/fields', [ ResourceController::class, 'fields' ]);
Route::get('/resources', [ ResourceController::class, 'list' ]);

/**
 * Relationship
 */
Route::get('/belongs-to/{resource}/{key}/{field}', [ BelongsToController::class, 'searchBelongsTo' ]);

/**
 * CRUD
 */
Route::get('/{resource}/{key}', [ DetailController::class, 'detail' ]);
Route::patch('/{resource}/{key}', [ UpdateController::class, 'update' ]);
Route::delete('/{resource}', [ DeleteController::class, 'delete' ]);
Route::post('/{resource}', [ StoreController::class, 'store' ]);
Route::get('/{resource}', [ IndexController::class, 'index' ]);
