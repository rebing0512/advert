<?php
/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Here is where you can register admin routes for your application. These
| routes are loaded by the RouteServiceProvider Now create something great!
|
*/
Route::group([
    'namespace' => 'Admin',
    'prefix' => config('mbcore_avdert.admin_prefix','Admin'),
],function(\Illuminate\Routing\Router $router) {

});