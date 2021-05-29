<?php

use DigitalCreative\Jaqen\Http\Controllers\Relationships\BelongsToController;
use DigitalCreative\Jaqen\Http\Controllers\Resources\DeleteController;
use DigitalCreative\Jaqen\Http\Controllers\Resources\DetailController;
use DigitalCreative\Jaqen\Http\Controllers\Resources\IndexController;
use DigitalCreative\Jaqen\Http\Controllers\Resources\StoreController;
use DigitalCreative\Jaqen\Http\Controllers\Resources\UpdateController;
use Illuminate\Support\Facades\Route;

/**
 * Relationship
 */
Route::get('/belongs-to/{resource}/{field}', [ BelongsToController::class, 'searchBelongsTo' ]);
