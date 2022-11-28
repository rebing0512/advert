<?php
/*
|--------------------------------------------------------------------------
| Client Routes
|--------------------------------------------------------------------------
|
| Here is where you can register client routes for your application. These
| routes are loaded by the RouteServiceProvider Now create something great!
|
*/
Route::group([
    'namespace' => 'Client',
    'prefix' => config('mbcore_avdert.client_prefix','Mini'),
],function(\Illuminate\Routing\Router $router) {
    $router->any('/madvert',function (){
        return 'Jenson advert web route';
    });
});