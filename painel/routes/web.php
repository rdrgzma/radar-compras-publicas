<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return auth()->user()->isAdmin() 
            ? redirect('/admin') 
            : redirect('/portal');
    }
    return redirect('/portal/login');
});

Route::get('/up', function () {
    return response('OK', 200)
        ->header('Content-Type', 'text/plain');
});
