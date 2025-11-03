<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/aHR0cHM6Ly9leGFt', function () {
    return view('welcome');
});

Route::get('/xdebug', function () {
    xdebug_info();
});

Route::post('store-location', function (Request $request) {
    logger()->info(json_encode($request->all()));
    session()->put('location', json_encode($request->all()));
});
