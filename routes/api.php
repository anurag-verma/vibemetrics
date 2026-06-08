<?php

use App\Http\Controllers\Api\CollectController;
use Illuminate\Support\Facades\Route;

Route::post('/collect', [CollectController::class, 'store'])
    ->middleware('throttle:collect');
