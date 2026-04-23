<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DomainConfigController;
use App\Http\Controllers\Api\DomainCheckController;

Route::get('/domain/config', [DomainConfigController::class, 'index']);
Route::post('/domain/check', [DomainCheckController::class, 'check']);