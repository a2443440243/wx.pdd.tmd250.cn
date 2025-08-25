<?php

// +----------------------------------------------------------------------
// | Index Module Auth Controller for ThinkAdmin
// +----------------------------------------------------------------------
// | 版权所有 2014~2025 ThinkAdmin [ thinkadmin.top ]
// +----------------------------------------------------------------------
// | 官方网站: https://thinkadmin.top
// +----------------------------------------------------------------------
// | 开源协议 ( https://mit-license.org )
// | 免责声明 ( https://thinkadmin.top/disclaimer )
// +----------------------------------------------------------------------

namespace app\index\controller;

use app\index\model\User;
use think\Request;
use think\Response;
use think\facade\Session;
use think\facade\View;

/**
 * 用户认证控制器
 * Class Auth
 * @package app\index\controller
 */
class Auth
{
    /**
     * 显示登录页面或处理登录请求
     * @param Request $request
     * @return string|Response
     */
    public function login(Request $request)
    {
        
        if ($request->isGet()) {
            // 如果已登录，跳转到首页
            if (Session::has('user_id')) {
                return redirect('/')->getContent();
            }
            
            // 记录来源页面
            $referer = $request->header('referer', '');
            if ($referer && strpos($referer, '/profile') !== false) {
                Session::set('login_redirect', '/profile');
            }
            
            return View::fetch('auth/login');
        } else {
            // 处理POST登录请求
            return $this->doLogin($request);
        }
    }

    /**
     * 处理登录请求
     * @param Request $request
     * @return Response
     */
    public function doLogin(Request $request): Response
    {
        $phone = $request->post('phone', '');
        $password = $request->post('password', '');
        $remember = $request->post('remember', 0);

        // 验证输入
        if (empty($phone) || empty($password)) {
            return json(['code' => 0, 'msg' => '手机号和密码不能为空']);
        }

        // 验证手机号格式
        if (!User::validatePhone($phone)) {
            return json(['code' => 0, 'msg' => '手机号格式不正确']);
        }

        // 尝试登录
        $result = User::login($phone, $password);
        
        if ($result['code'] === 1) {
            // 登录成功，设置session
            Session::set('user_id', $result['data']['id']);
            Session::set('user_info', $result['data']);
            
            // 记住登录状态
            if ($remember) {
                cookie('remember_token', md5($phone . time()), 7 * 24 * 3600);
            }
            
            // 获取跳转地址
            $redirectUrl = Session::get('login_redirect', '/profile');
            Session::delete('login_redirect');
            
            return json(['code' => 1, 'msg' => '登录成功', 'url' => $redirectUrl]);
        }
        
        return json($result);
    }

    /**
     * 显示注册页面
     * @return string
     */
    public function register(): string
    {
        // 如果已登录，跳转到首页
        if (Session::has('user_id')) {
            return redirect('/')->getContent();
        }
        
        return View::fetch('auth/register');
    }

    /**
     * 处理注册请求
     * @param Request $request
     * @return Response
     */
    public function doRegister(Request $request): Response
    {
        $phone = $request->post('phone', '');
        $password = $request->post('password', '');
        $confirm_password = $request->post('confirm_password', '');
        $nickname = $request->post('nickname', '');
        $sms_code = $request->post('sms_code', '');

        // 验证输入
        if (empty($phone) || empty($password) || empty($confirm_password)) {
            return json(['code' => 0, 'msg' => '手机号、密码和确认密码不能为空']);
        }

        // 验证手机号格式
        if (!User::validatePhone($phone)) {
            return json(['code' => 0, 'msg' => '手机号格式不正确']);
        }

        // 验证密码长度
        if (strlen($password) < 6) {
            return json(['code' => 0, 'msg' => '密码长度不能少于6位']);
        }

        // 验证密码确认
        if ($password !== $confirm_password) {
            return json(['code' => 0, 'msg' => '两次输入的密码不一致']);
        }

        // 验证短信验证码（暂时跳过，后续实现）
        // if (empty($sms_code)) {
        //     return json(['code' => 0, 'msg' => '请输入短信验证码']);
        // }

        // 尝试注册
        $result = User::register($phone, $password, $nickname);
        
        if ($result['code'] === 1) {
            // 注册成功，自动登录
            Session::set('user_id', $result['data']['id']);
            Session::set('user_info', $result['data']);
            
            return json(['code' => 1, 'msg' => '注册成功', 'url' => '/']);
        }
        
        return json($result);
    }

    /**
     * 用户退出登录
     * @return Response
     */
    public function logout(): Response
    {
        Session::delete('user_id');
        Session::delete('user_info');
        cookie('remember_token', null);
        
        return json(['code' => 1, 'msg' => '退出成功', 'url' => '/']);
    }

    /**
     * 发送短信验证码
     * @param Request $request
     * @return Response
     */
    public function sendSms(Request $request): Response
    {
        $phone = $request->post('phone', '');
        
        // 验证手机号格式
        if (!User::validatePhone($phone)) {
            return json(['code' => 0, 'msg' => '手机号格式不正确']);
        }
        
        // 生成验证码
        $code = sprintf('%06d', mt_rand(0, 999999));
        
        // 存储验证码到session（5分钟有效）
        Session::set('sms_code_' . $phone, $code);
        Session::set('sms_time_' . $phone, time());
        
        // TODO: 这里应该调用短信服务发送验证码
        // 暂时返回成功，实际项目中需要集成短信服务
        
        return json(['code' => 1, 'msg' => '验证码发送成功']);
    }

    /**
     * 验证短信验证码
     * @param string $phone
     * @param string $code
     * @return bool
     */
    private function verifySmsCode(string $phone, string $code): bool
    {
        $stored_code = Session::get('sms_code_' . $phone);
        $stored_time = Session::get('sms_time_' . $phone);
        
        // 检查验证码是否存在
        if (empty($stored_code) || empty($stored_time)) {
            return false;
        }
        
        // 检查验证码是否过期（5分钟）
        if (time() - $stored_time > 300) {
            Session::delete('sms_code_' . $phone);
            Session::delete('sms_time_' . $phone);
            return false;
        }
        
        // 验证验证码
        if ($stored_code === $code) {
            Session::delete('sms_code_' . $phone);
            Session::delete('sms_time_' . $phone);
            return true;
        }
        
        return false;
    }
}