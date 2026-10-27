<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// temporary solution for Route [login] error
Route::post('/login', function() {
    return response()->json(['message' => 'Need login'], 200);
})->name('login');
