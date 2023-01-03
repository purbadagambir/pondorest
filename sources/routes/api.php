<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

# Member
Route::post('/auth', 'ApiCustomerController@memberLogin');
Route::post('/member', 'ApiCustomerController@getMember');
Route::post('/customerCredit', 'ApiCustomerController@customerCredit');
Route::post('/point', 'ApiCustomerController@getPointData');

# Transactions
Route::post('/salesInfo', 'ApiSalesController@getSalesInfo');
Route::post('/addPoint', 'ApiSalesController@insertSalesData');
Route::post('/addCredit', 'ApiSalesController@insertCredit');
