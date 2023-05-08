<?php

use Illuminate\Support\Facades\Route;

Route::middleware('api')->prefix('api/sso')->group(function () {
    Route::post('logout', 'Esyede\SSO\Controllers\ServerController@logout');
    Route::get('attach', 'Esyede\SSO\Controllers\ServerController@attach');

    if (config('sso.multi_enabled')) {
        Route::get('userInfoMulti', 'Esyede\SSO\Controllers\ServerController@userInfoMulti');
        Route::post('loginMulti', 'Esyede\SSO\Controllers\ServerController@loginMulti');
    } else {
        Route::post('login', 'Esyede\SSO\Controllers\ServerController@login');
        Route::get('userInfo', 'Esyede\SSO\Controllers\ServerController@userInfo');
    }

    if (config('sso.api.enabled')) {
        Route::post('check', 'Esyede\SSO\Controllers\ServerController@check');
    }
});
