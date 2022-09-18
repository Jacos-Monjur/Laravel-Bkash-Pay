<?php

use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'monjur\bkash\Http\Controllers'], function () {
    Route::get('demo-order', 'BkashPayController@index')->name('order');
    Route::get('order-show/{id}', 'BkashPayController@show')->name('show');
    Route::post('token', 'BkashPayController@token')->name('token');
    Route::get('createpayment', 'BkashPayController@createpayment')->name('createpayment');
    Route::get('executepayment', 'BkashPayController@executepayment')->name('executepayment');
});
