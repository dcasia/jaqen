<?php

use DigitalCreative\Jaqen\Services\ResourceManager\Http\Controllers\FiltersController;
use DigitalCreative\Jaqen\Http\Controllers\Resources\DeleteController;
use DigitalCreative\Jaqen\Http\Controllers\Resources\DetailController;
use DigitalCreative\Jaqen\Services\ResourceManager\Http\Controllers\FieldsController;
use DigitalCreative\Jaqen\Http\Controllers\Resources\IndexController;
use DigitalCreative\Jaqen\Http\Controllers\Relationships\BelongsToController;
use DigitalCreative\Jaqen\Http\Controllers\ResourceController;
use DigitalCreative\Jaqen\Http\Controllers\Resources\StoreController;
use DigitalCreative\Jaqen\Http\Controllers\Resources\UpdateController;
use Illuminate\Support\Facades\Route;

/**
 * Filters
 */
Route::get('/resources', [ ResourceController::class, 'resources' ]);

/**
 * Relationship
 */
Route::get('/belongs-to/{resource}/{field}', [ BelongsToController::class, 'searchBelongsTo' ]);
