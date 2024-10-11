<?php

use App\Http\Controllers\FrontController;
use App\Http\Controllers\PaymeController;
use App\Http\Middleware\PaymeCheck;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('bot', FrontController::class);
Route::post('payme', [PaymeController::class, 'handle'])->middleware(PaymeCheck::class);