<?php

// +----------------------------------------------------------------------
// | Index Module Controller for ThinkAdmin
// +----------------------------------------------------------------------
// | 版权所有 2014~2024 ThinkAdmin [ thinkadmin.top ]
// +----------------------------------------------------------------------
// | 官方网站: https://thinkadmin.top
// +----------------------------------------------------------------------
// | 开源协议 ( https://mit-license.org )
// | 免责声明 ( https://thinkadmin.top/disclaimer )
// +----------------------------------------------------------------------

namespace app\index\controller;

use think\admin\Controller;
use app\index\service\IndexService;
use app\index\service\AuthService;

/**
 * 前台首页控制器
 * Class Index
 * @package app\index\controller
 */
class Index extends Controller
{
    /**
     * 首页展示
     * @return string|null
     */
    public function index()
    {
        // 获取轮播图数据
        $bannerList = IndexService::getBannerList();
        
        // 获取核心业务数据
        $coreServices = IndexService::getCoreServices();
        
        // 检查用户登录状态
        $currentUser = AuthService::getCurrentUser();
        $isLoggedIn = AuthService::isLoggedIn();
        
        // 导航菜单数据
        $nav_menu = [
            ['title' => '首页', 'url' => '/', 'active' => true],
            ['title' => '产品功能', 'url' => '#features'],
            ['title' => '解决方案', 'url' => '/solutions'],
            ['title' => '价格方案', 'url' => '/pricing'],
            ['title' => '帮助中心', 'url' => '/help'],
            ['title' => '联系我们', 'url' => '/contact']
        ];
        
        // 根据登录状态添加用户相关菜单
        if ($isLoggedIn) {
            $nav_menu[] = ['title' => '个人中心', 'url' => '/user/profile'];
            $nav_menu[] = ['title' => '退出登录', 'url' => '/auth/logout'];
        } else {
            $nav_menu[] = ['title' => '登录', 'url' => '/auth/login'];
            $nav_menu[] = ['title' => '注册', 'url' => '/auth/register'];
        }
        
        // 站点配置
        $site_config = [
            'site_name' => '活码官网',
            'site_description' => '专业的活码生成和管理平台，提供智能二维码营销解决方案',
            'keywords' => '活码,二维码,营销工具,智能营销',
            'title' => '首页'
        ];
        
        // 设置页面标题和关键词
        $this->assign([
            'title'         => '首页',
            'keywords'      => 'ThinkAdmin,管理系统,PHP框架',
            'description'   => 'ThinkAdmin 是一个基于 ThinkPHP 6.0 开发的后台管理系统',
            'banner_list'   => $bannerList,
            'core_services' => $coreServices,
            'nav_menu'      => $nav_menu,
            'site_config'   => $site_config,
            'current_user'  => $currentUser,
            'is_logged_in'  => $isLoggedIn,
        ]);
        
        return $this->fetch();
    }
    
    /**
     * 重定向到管理后台（兼容旧版本）
     * @return void
     */
    public function admin(): void
    {
        $this->redirect(sysuri('admin/login/index'));
    }
}
