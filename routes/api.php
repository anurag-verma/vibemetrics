<?php

use App\Http\Controllers\Api\CollectController;
use App\Http\Controllers\Api\CollectEventController;
use Illuminate\Support\Facades\Route;

Route::post('/collect', [CollectController::class, 'store'])
    ->middleware('throttle:collect');

Route::post('/event', [CollectEventController::class, 'store'])
    ->middleware('throttle:collect');
