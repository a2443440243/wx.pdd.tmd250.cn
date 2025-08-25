<?php
// +----------------------------------------------------------------------
// | ThinkAdmin
// +----------------------------------------------------------------------
// | 版权所有 2014~2024 ThinkAdmin [ thinkadmin.top ]
// +----------------------------------------------------------------------
// | 官方网站: https://thinkadmin.top
// +----------------------------------------------------------------------
// | 开源协议 ( https://mit-license.org )
// +----------------------------------------------------------------------
// | gitee 代码仓库：https://gitee.com/zoujingli/ThinkAdmin
// | github 代码仓库：https://github.com/zoujingli/ThinkAdmin
// +----------------------------------------------------------------------

use think\facade\Route;

// 个人中心路由
Route::group('profile', function () {
    // 个人中心首页
    Route::get('/', 'Profile/index');
    
    // 个人信息管理
    Route::get('info', 'Profile/info');
    Route::post('updateInfo', 'Profile/updateInfo');
    
    // 密码修改
    Route::get('password', 'Profile/password');
    Route::post('updatePassword', 'Profile/updatePassword');
    
    // 头像上传
    Route::get('avatar', 'Profile/avatar');
    Route::post('uploadAvatar', 'Profile/uploadAvatar');
    
    // 登录记录
    Route::get('loginlog', 'Profile/loginlog');
    Route::get('getLoginLog', 'Profile/getLoginLog');
});