<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProductController;

Route::get('/', [PageController::class, 'parsePageForm']);
Route::post('/', [PageController::class, 'parsePage']);


