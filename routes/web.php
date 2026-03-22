<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('pos.index');
});

// Handle all SPA routes
Route::get('/{path?}', function () {
    return view('pos.index');
})->where('path', '.*');
