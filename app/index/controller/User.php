<?php

namespace app\index\controller;

use think\admin\Controller;
use think\response\Redirect;

/**
 * 用户控制器（兼容旧路由）
 * Class User
 * @package app\index\controller
 */
class User extends Controller
{
    /**
     * 个人中心页面（重定向到Profile控制器）
     * @return Redirect
     */
    public function profile(): Redirect
    {
        return redirect('/index/profile');
    }
    
    /**
     * 用户信息（重定向到Profile控制器）
     * @return Redirect
     */
    public function info(): Redirect
    {
        return redirect('/index/profile');
    }
    
    /**
     * 修改密码（重定向到Profile控制器）
     * @return Redirect
     */
    public function password(): Redirect
    {
        return redirect('/index/profile');
    }
    
    /**
     * 头像上传（重定向到Profile控制器）
     * @return Redirect
     */
    public function avatar(): Redirect
    {
        return redirect('/index/profile');
    }
}