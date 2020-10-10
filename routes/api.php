<?php

use DigitalCreative\Dashboard\Http\Controllers\FiltersController;
use DigitalCreative\Dashboard\Http\Controllers\Resources\DeleteController;
use DigitalCreative\Dashboard\Http\Controllers\Resources\DetailController;
use DigitalCreative\Dashboard\Http\Controllers\FieldsController;
use DigitalCreative\Dashboard\Http\Controllers\Resources\IndexController;
use DigitalCreative\Dashboard\Http\Controllers\Relationships\BelongsToController;
use DigitalCreative\Dashboard\Http\Controllers\ResourceController;
use DigitalCreative\Dashboard\Http\Controllers\Resources\StoreController;
use DigitalCreative\Dashboard\Http\Controllers\Resources\UpdateController;
use Illuminate\Support\Facades\Route;

/**
 * Filters
 */
Route::get('/{resource}/filters', [ FiltersController::class, 'filters' ]);
Route::get('/{resource}/fields', [ FieldsController::class, 'fields' ]);
Route::get('/resources', [ ResourceController::class, 'resources' ]);

/**
 * Relationship
 */
Route::get('/belongs-to/{resource}/{key}/{field}', [ BelongsToController::class, 'searchBelongsTo' ]);

/**
 * CRUD
 */
Route::get('/{resource}/{key}', [ DetailController::class, 'handle' ]);
Route::patch('/{resource}/{key}', [ UpdateController::class, 'handle' ]);
Route::delete('/{resource}', [ DeleteController::class, 'handle' ]);
Route::post('/{resource}', [ StoreController::class, 'handle' ]);
Route::get('/{resource}', [ IndexController::class, 'handle' ]);
