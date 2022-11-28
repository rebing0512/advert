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
    $router->any('/madvert',function (){
        return 'Jenson advert admin route';
    });
    /*
     |--------
     | 广告/广告分类/广告类型
     |--------
   */
    Route::group([
        'namespace' => 'Advert',
        'prefix' => 'advert'
    ], function (\Illuminate\Routing\Router $router) {
        #广告列表
        $router->any('index', 'AdvertController@index');
        #添加广告
        $router->any('add', 'AdvertController@add');
        #选择分类
        $router->any('chooseCate', 'AdvertController@chooseCate');
        #选择类型
        $router->any('chooseType', 'AdvertController@chooseType');
        #选择ID
        $router->any('chooseId', 'AdvertController@chooseId');
        #编辑
        $router->any('edit', 'AdvertController@edit');
        #修改排序
        $router->any('changeSort/{adv_id}', 'AdvertController@changeSort')->where('adv_id', '\d+');
        #显示广告
        $router->any('show', 'AdvertController@show');
        #删除广告
        $router->any('delete', 'AdvertController@delete');
        $router->any('checkInfo', 'AdvertController@checkInfo');
        # 小程序路径
        $router->any('miniprogram', 'AdvertController@miniprogram');
        # 广告类型
        Route::group([
            'prefix' => 'category'
        ], function (\Illuminate\Routing\Router $router) {
            # 分类列表
            $router->any('index', 'AdvertCategoryController@index');
            # 添加分类
            $router->any('add', 'AdvertCategoryController@add');
            # 分类详情
            $router->any('show', 'AdvertCategoryController@show');
            # 编辑分类
            $router->any('edit', 'AdvertCategoryController@edit');
            # 删除分类
            $router->any('delete', 'AdvertCategoryController@delete');
        });
    });
});