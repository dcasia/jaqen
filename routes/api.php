<?php

use DigitalCreative\Jaqen\Http\Controllers\FiltersController;
use DigitalCreative\Jaqen\Http\Controllers\Resources\DeleteController;
use DigitalCreative\Jaqen\Http\Controllers\Resources\DetailController;
use DigitalCreative\Jaqen\Http\Controllers\FieldsController;
use DigitalCreative\Jaqen\Http\Controllers\Resources\IndexController;
use DigitalCreative\Jaqen\Http\Controllers\Relationships\BelongsToController;
use DigitalCreative\Jaqen\Http\Controllers\ResourceController;
use DigitalCreative\Jaqen\Http\Controllers\Resources\StoreController;
use DigitalCreative\Jaqen\Http\Controllers\Resources\UpdateController;
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
Route::get('/belongs-to/{resource}/{field}', [ BelongsToController::class, 'searchBelongsTo' ]);

/**
 * CRUD
 */
Route::get('/{resource}/{key}', [ DetailController::class, 'handle' ]);
Route::patch('/{resource}/{key}', [ UpdateController::class, 'handle' ]);
Route::delete('/{resource}', [ DeleteController::class, 'handle' ]);
Route::post('/{resource}', [ StoreController::class, 'handle' ]);
Route::get('/{resource}', [ IndexController::class, 'handle' ]);
