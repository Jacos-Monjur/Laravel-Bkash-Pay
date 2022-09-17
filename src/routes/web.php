<?php

use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'monjur\bkashpay\Http\Controllers'], function () {
    Route::get('demo-order', 'PaymentController@index')->name('order');
    Route::post('token', 'PaymentController@token')->name('token');
    Route::get('createpayment', 'PaymentController@createpayment')->name('createpayment');
    Route::get('executepayment', 'PaymentController@executepayment')->name('executepayment');
});
