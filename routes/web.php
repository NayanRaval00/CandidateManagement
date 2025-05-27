<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

Route::get('/', function () {
    return view('welcome');
});

Route::get('clear-route', function () {
    Artisan::call('route:clear');
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    Artisan::call('config:cache');
    Artisan::call('route:cache');
    Artisan::call('view:clear');
    return '<h1>Routes has been cleared.</h1>';
});

Route::get('save-details', [UserController::class, 'save']);
Route::post('/users', [UserController::class, 'store'])->name('users.store');

