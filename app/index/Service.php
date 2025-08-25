<?php

// +----------------------------------------------------------------------
// | Index Module Service for ThinkAdmin
// +----------------------------------------------------------------------
// | 版权所有 2014~2024 ThinkAdmin [ thinkadmin.top ]
// +----------------------------------------------------------------------
// | 官方网站: https://thinkadmin.top
// +----------------------------------------------------------------------
// | 开源协议 ( https://mit-license.org )
// | 免责声明 ( https://thinkadmin.top/disclaimer )
// +----------------------------------------------------------------------

namespace app\index;

use think\admin\Plugin;
use think\admin\service\AdminService;

/**
 * 前台模块服务
 * Class Service
 * @package app\index
 */
class Service extends Plugin
{
    /**
     * 定义插件名称
     * @var string
     */
    protected $appName = '前台模块';

    /**
     * 定义安装包名
     * @var string
     */
    protected $package = 'xiaochao/think-plugs-index';

    /**
     * 插件服务注册
     */
    public function register(): void
    {
        // 注册前台服务
        $this->app->bind('IndexService', \app\index\service\IndexService::class);
    }

    /**
     * 插件服务启动
     */
    public function boot(): void
    {
        // 注册前台菜单
        $this->registerMenu();
    }

    /**
     * 注册前台菜单
     */
    private function registerMenu(): void
    {
        // 这里可以注册前台相关的后台管理菜单
        // AdminService::instance()->addMenu([
        //     'title' => '前台管理',
        //     'icon'  => 'layui-icon layui-icon-website',
        //     'node'  => 'index/config/index'
        // ]);
    }

    /**
     * 获取模块配置
     * @return array
     */
    public static function getConfig(): array
    {
        return [
            'name'        => '前台模块',
            'description' => 'ThinkAdmin 前台展示模块',
            'version'     => '1.0.0',
            'author'      => 'ThinkAdmin',
            'homepage'    => 'https://thinkadmin.top'
        ];
    }
}