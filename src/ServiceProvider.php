<?php
namespace MBCore\MAdvert;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Illuminate\Routing\Router;
use MBCore\MAdvert\Commands\DistributorExpired;
use MBCore\MAdvert\Commands\PreBindExpired;

class ServiceProvider extends BaseServiceProvider{

    /**
     * 在注册后进行服务的启动。
     *
     * @return void
     */
	public function boot()
	{
        # 发布扩展包的配置文件
        $this->publishes([
            __DIR__ . '/config/mbcore_madvert.php' => config_path('mbcore_madvert.php'),
        ], 'config');
        # 加载路由
        $this->setupRoutes($this->app->router);
        # 数据库迁移
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
        # 注册 Artisan 命令
        if ($this->app->runningInConsole()) {
            $this->commands([
                # do something
            ]);
        }
    }

	/**
	 * 为控制器组指定公共的 PHP 命名空间
	 *
	 * @param  \Illuminate\Routing\Router  $router
	 * @return void
	 */
	public function setupRoutes(Router $router)
	{
		$router->group(['namespace' => 'MBCore\MAdvert\Controllers'], function($router)
		{
            require __DIR__.'/routes/client.php';
            require __DIR__.'/routes/admin.php';
		});
	}

    /**
     * 在容器中注册绑定。
     *
     * @return void
     */
	public function register()
	{
        # 默认的包配置
        $this->mergeConfigFrom(
            __DIR__ . '/config/mbcore_madvert.php', 'mbcore_madvert'
        );
	}
}
