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

namespace app\index\service;

use think\admin\Service;
use think\admin\Exception;

/**
 * 前台业务服务
 * Class IndexService
 * @package app\index\service
 */
class IndexService extends Service
{
    /**
     * 错误信息
     * @var string
     */
    protected static $error = '';
    /**
     * 获取网站配置
     * @return array
     */
    public static function getWebConfig(): array
    {
        return [
            'site_name'        => sysconf('site_name') ?: 'ThinkAdmin',
            'site_title'       => sysconf('site_title') ?: 'ThinkAdmin 管理系统',
            'site_keywords'    => sysconf('site_keywords') ?: 'ThinkAdmin,管理系统',
            'site_description' => sysconf('site_description') ?: 'ThinkAdmin 是一个基于 ThinkPHP 6.0 开发的后台管理系统',
            'site_logo'        => sysconf('site_logo') ?: '/static/theme/img/logo.png',
            'site_icon'        => sysconf('site_icon') ?: '/static/theme/img/favicon.ico',
            'site_copyright'   => sysconf('site_copyright') ?: 'Copyright © 2024 ThinkAdmin. All rights reserved.',
        ];
    }

    /**
     * 获取导航菜单
     * @return array
     */
    public static function getNavMenu(): array
    {
        return [
            ['title' => '首页', 'url' => '/', 'active' => true],
            ['title' => '关于我们', 'url' => '/about', 'active' => false],
            ['title' => '产品服务', 'url' => '/products', 'active' => false, 'children' => [
                ['title' => '产品介绍', 'url' => '/products/intro'],
                ['title' => '解决方案', 'url' => '/products/solutions'],
                ['title' => '技术支持', 'url' => '/products/support'],
            ]],
            ['title' => '新闻资讯', 'url' => '/news', 'active' => false],
            ['title' => '联系我们', 'url' => '/contact', 'active' => false],
        ];
    }

    /**
     * 获取轮播图数据
     * @return array
     */
    public static function getBannerList(): array
    {
        return [
            [
                'id'    => 1,
                'title' => '欢迎使用 ThinkAdmin',
                'desc'  => '基于 ThinkPHP 6.0 开发的后台管理系统',
                'image' => '/static/theme/img/banner1.jpg',
                'url'   => '/about',
            ],
            [
                'id'    => 2,
                'title' => '高效开发',
                'desc'  => '提供丰富的组件和插件，助力快速开发',
                'image' => '/static/theme/img/banner2.jpg',
                'url'   => '/products',
            ],
            [
                'id'    => 3,
                'title' => '安全可靠',
                'desc'  => '完善的权限管理和安全防护机制',
                'image' => '/static/theme/img/banner3.jpg',
                'url'   => '/contact',
            ],
        ];
    }

    /**
     * 获取核心业务介绍
     * @return array
     */
    public static function getCoreServices(): array
    {
        return [
            [
                'icon'  => 'layui-icon-component',
                'title' => '组件丰富',
                'desc'  => '提供丰富的UI组件和业务组件，开箱即用',
            ],
            [
                'icon'  => 'layui-icon-engine',
                'title' => '高性能',
                'desc'  => '基于ThinkPHP 6.0框架，性能优异，稳定可靠',
            ],
            [
                'icon'  => 'layui-icon-security',
                'title' => '安全防护',
                'desc'  => '完善的权限管理和安全防护，保障系统安全',
            ],
            [
                'icon'  => 'layui-icon-cellphone',
                'title' => '响应式设计',
                'desc'  => '支持多终端访问，PC、平板、手机完美适配',
            ],
        ];
    }

    /**
     * 发送邮件
     * @param string $to
     * @param string $subject
     * @param string $content
     * @return bool
     */
    public static function sendMail(string $to, string $subject, string $content): bool
    {
        try {
            // 这里可以集成邮件发送服务
            // 例如：使用 think-mailer 或其他邮件服务
            return true;
        } catch (Exception $e) {
            static::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * 记录访问日志
     * @param array $data
     * @return bool
     */
    public static function logVisit(array $data): bool
    {
        try {
            // 记录访问日志到数据库或文件
            $logData = [
                'ip'         => request()->ip(),
                'user_agent' => request()->header('user-agent'),
                'url'        => request()->url(true),
                'method'     => request()->method(),
                'params'     => json_encode(request()->param()),
                'create_time' => date('Y-m-d H:i:s'),
            ];
            
            // 这里可以保存到数据库
            // static::mSave('IndexVisitLog', array_merge($logData, $data));
            
            return true;
        } catch (Exception $e) {
            static::$error = $e->getMessage();
            return false;
        }
    }
}