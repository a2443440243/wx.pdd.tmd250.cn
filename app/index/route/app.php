<?php

// +----------------------------------------------------------------------
// | Index Module Routes for ThinkAdmin
// +----------------------------------------------------------------------
// | 版权所有 2014~2024 ThinkAdmin [ thinkadmin.top ]
// +----------------------------------------------------------------------
// | 官方网站: https://thinkadmin.top
// +----------------------------------------------------------------------
// | 开源协议 ( https://mit-license.org )
// | 免责声明 ( https://thinkadmin.top/disclaimer )
// +----------------------------------------------------------------------

use think\facade\Route;

// 前台首页路由
Route::group('/', function () {
    // 首页
    Route::get('/', 'Index/index');
    Route::get('index', 'Index/index');
    Route::get('home', 'Index/index');
    
    // 关于我们
    Route::get('about', 'About/index');
    Route::get('about/[:id]', 'About/detail');
    
    // 产品服务
    Route::group('products', function () {
        Route::get('/', 'Products/index');
        Route::get('intro', 'Products/intro');
        Route::get('solutions', 'Products/solutions');
        Route::get('support', 'Products/support');
        Route::get('detail/[:id]', 'Products/detail');
    });
    
    // 新闻资讯
    Route::group('news', function () {
        Route::get('/', 'News/index');
        Route::get('detail/[:id]', 'News/detail');
        Route::get('category/[:id]', 'News/category');
    });
    
    // 联系我们
    Route::get('contact', 'Contact/index');
    Route::post('contact/submit', 'Contact/submit');
    
    // 用户认证相关
    Route::group('auth', function () {
        Route::get('login', 'Auth/login');
        Route::post('login', 'Auth/login');
        Route::post('doLogin', 'Auth/login');
        Route::get('register', 'Auth/register');
        Route::post('register', 'Auth/register');
        Route::post('doRegister', 'Auth/register');
        Route::get('logout', 'Auth/logout');
    });
    
    // 个人中心相关
    Route::group('profile', function () {
        Route::get('/', 'Profile/index');
        Route::get('info', 'Profile/info');
        Route::post('updateInfo', 'Profile/updateInfo');
        Route::post('changePassword', 'Profile/changePassword');
        Route::post('uploadAvatar', 'Profile/uploadAvatar');
        Route::get('loginHistory', 'Profile/loginHistory');
    });
    
    // 兼容旧的用户路由
    Route::group('user', function () {
        Route::get('login', 'Auth/login');
        Route::post('login', 'Auth/login');
        Route::get('register', 'Auth/register');
        Route::post('register', 'Auth/register');
        Route::get('logout', 'Auth/logout');
        Route::get('profile', 'Profile/index');
         Route::post('profile', 'Profile/updateInfo');
     });
    
    // API 接口
    Route::group('api', function () {
        // 获取网站配置
        Route::get('config', 'Api/getConfig');
        // 获取导航菜单
        Route::get('menu', 'Api/getMenu');
        // 获取轮播图
        Route::get('banner', 'Api/getBanner');
        // 获取核心业务
        Route::get('services', 'Api/getServices');
        // 搜索
        Route::get('search', 'Api/search');
        // 留言提交
        Route::post('message', 'Api/submitMessage');
    });
    
})->middleware(\app\index\middleware\IndexMiddleware::class);

// 静态资源路由（可选）
Route::group('static', function () {
    Route::get('captcha', 'Static/captcha');
    Route::get('qrcode', 'Static/qrcode');
});

// 错误页面路由
Route::get('error/[:code]', 'Error/index');
Route::get('404', 'Error/notFound');
Route::get('403', 'Error/forbidden');
Route::get('500', 'Error/serverError');