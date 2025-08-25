<?php

// +----------------------------------------------------------------------
// | Index Module Auth Service for ThinkAdmin
// +----------------------------------------------------------------------
// | 版权所有 2014~2025 ThinkAdmin [ thinkadmin.top ]
// +----------------------------------------------------------------------
// | 官方网站: https://thinkadmin.top
// +----------------------------------------------------------------------
// | 开源协议 ( https://mit-license.org )
// | 免责声明 ( https://thinkadmin.top/disclaimer )
// +----------------------------------------------------------------------

namespace app\index\service;

use app\index\model\User;
use think\facade\Session;
use think\facade\Cache;

/**
 * 用户认证服务类
 * Class AuthService
 * @package app\index\service
 */
class AuthService
{
    /**
     * 获取当前登录用户信息
     * @return array|null
     */
    public static function getCurrentUser(): ?array
    {
        $userId = Session::get('user_id');
        if (empty($userId)) {
            return null;
        }
        
        $userInfo = Session::get('user_info');
        if (empty($userInfo)) {
            // 从数据库重新获取用户信息
            $user = User::where('id', $userId)
                ->where('status', 1)
                ->where('deleted', 0)
                ->find();
            
            if ($user) {
                $userInfo = $user->hidden(['password'])->toArray();
                Session::set('user_info', $userInfo);
            } else {
                // 用户不存在或被禁用，清除session
                static::logout();
                return null;
            }
        }
        
        return $userInfo;
    }

    /**
     * 检查用户是否已登录
     * @return bool
     */
    public static function isLogin(): bool
    {
        return !empty(static::getCurrentUser());
    }

    /**
     * 检查用户是否已登录
     * @return bool
     */
    public static function isLoggedIn(): bool
    {
        return !is_null(self::getCurrentUser());
    }

    /**
     * 用户登录
     * @param string $phone 手机号
     * @param string $password 密码
     * @param bool $remember 是否记住登录
     * @return array
     */
    public static function login(string $phone, string $password, bool $remember = false): array
    {
        // 检查登录频率限制
        $cacheKey = 'login_attempt_' . $phone;
        $attempts = Cache::get($cacheKey, 0);
        
        if ($attempts >= 5) {
            return ['code' => 0, 'msg' => '登录尝试次数过多，请30分钟后再试'];
        }
        
        // 尝试登录
        $result = User::login($phone, $password);
        
        if ($result['code'] === 1) {
            // 登录成功，清除失败次数
            Cache::delete($cacheKey);
            
            // 设置session
            Session::set('user_id', $result['data']['id']);
            Session::set('user_info', $result['data']);
            
            // 记住登录状态
            if ($remember) {
                $token = md5($phone . time() . uniqid());
                cookie('remember_token', $token, 7 * 24 * 3600);
                Cache::set('remember_' . $token, $result['data']['id'], 7 * 24 * 3600);
            }
            
            // 记录登录日志
            static::logUserAction($result['data']['id'], 'login', '用户登录');
            
        } else {
            // 登录失败，增加失败次数
            Cache::set($cacheKey, $attempts + 1, 1800); // 30分钟
        }
        
        return $result;
    }

    /**
     * 用户注册
     * @param string $phone 手机号
     * @param string $password 密码
     * @param string $nickname 昵称
     * @param string $smsCode 短信验证码
     * @return array
     */
    public static function register(string $phone, string $password, string $nickname = '', string $smsCode = ''): array
    {
        // 验证短信验证码
        if (!empty($smsCode) && !static::verifySmsCode($phone, $smsCode)) {
            return ['code' => 0, 'msg' => '短信验证码错误或已过期'];
        }
        
        // 尝试注册
        $result = User::register($phone, $password, $nickname);
        
        if ($result['code'] === 1) {
            // 注册成功，自动登录
            Session::set('user_id', $result['data']['id']);
            Session::set('user_info', $result['data']);
            
            // 记录注册日志
            static::logUserAction($result['data']['id'], 'register', '用户注册');
        }
        
        return $result;
    }

    /**
     * 用户退出登录
     * @return bool
     */
    public static function logout(): bool
    {
        $userId = Session::get('user_id');
        
        // 清除session
        Session::delete('user_id');
        Session::delete('user_info');
        
        // 清除记住登录的cookie和缓存
        $rememberToken = cookie('remember_token');
        if ($rememberToken) {
            cookie('remember_token', null);
            Cache::delete('remember_' . $rememberToken);
        }
        
        // 记录退出日志
        if ($userId) {
            static::logUserAction($userId, 'logout', '用户退出');
        }
        
        return true;
    }

    /**
     * 发送短信验证码
     * @param string $phone 手机号
     * @param string $type 验证码类型 (register|login|reset)
     * @return array
     */
    public static function sendSmsCode(string $phone, string $type = 'register'): array
    {
        // 检查发送频率
        $cacheKey = 'sms_send_' . $phone;
        $lastSendTime = Cache::get($cacheKey);
        
        if ($lastSendTime && (time() - $lastSendTime) < 60) {
            return ['code' => 0, 'msg' => '发送过于频繁，请稍后再试'];
        }
        
        // 生成验证码
        $code = sprintf('%06d', mt_rand(100000, 999999));
        
        // 存储验证码
        $smsKey = 'sms_code_' . $phone . '_' . $type;
        Cache::set($smsKey, $code, 300); // 5分钟有效
        Cache::set($cacheKey, time(), 60); // 发送间隔1分钟
        
        // TODO: 调用短信服务发送验证码
        // 这里应该集成实际的短信服务提供商
        
        return ['code' => 1, 'msg' => '验证码发送成功'];
    }

    /**
     * 验证短信验证码
     * @param string $phone 手机号
     * @param string $code 验证码
     * @param string $type 验证码类型
     * @return bool
     */
    public static function verifySmsCode(string $phone, string $code, string $type = 'register'): bool
    {
        $smsKey = 'sms_code_' . $phone . '_' . $type;
        $storedCode = Cache::get($smsKey);
        
        if (empty($storedCode)) {
            return false;
        }
        
        if ($storedCode === $code) {
            Cache::delete($smsKey);
            return true;
        }
        
        return false;
    }

    /**
     * 通过记住登录token自动登录
     * @param string $token
     * @return bool
     */
    public static function loginByRememberToken(string $token): bool
    {
        $userId = Cache::get('remember_' . $token);
        
        if (empty($userId)) {
            return false;
        }
        
        $user = User::where('id', $userId)
            ->where('status', 1)
            ->where('deleted', 0)
            ->find();
        
        if ($user) {
            $userInfo = $user->hidden(['password'])->toArray();
            Session::set('user_id', $user['id']);
            Session::set('user_info', $userInfo);
            
            return true;
        }
        
        // 用户不存在，清除token
        Cache::delete('remember_' . $token);
        return false;
    }

    /**
     * 记录用户操作日志
     * @param int $userId 用户ID
     * @param string $action 操作类型
     * @param string $description 操作描述
     * @return void
     */
    private static function logUserAction(int $userId, string $action, string $description): void
    {
        // TODO: 实现用户操作日志记录
        // 可以记录到数据库或日志文件
    }

    /**
     * 修改密码
     * @param int $userId 用户ID
     * @param string $oldPassword 旧密码
     * @param string $newPassword 新密码
     * @return array
     */
    public static function changePassword(int $userId, string $oldPassword, string $newPassword): array
    {
        $user = User::find($userId);
        
        if (!$user) {
            return ['code' => 0, 'msg' => '用户不存在'];
        }
        
        // 验证旧密码
        if ($user['password'] !== md5($oldPassword . 'thinkadmin')) {
            return ['code' => 0, 'msg' => '原密码错误'];
        }
        
        // 更新密码
        $result = $user->save(['password' => $newPassword]);
        
        if ($result) {
            static::logUserAction($userId, 'change_password', '修改密码');
            return ['code' => 1, 'msg' => '密码修改成功'];
        }
        
        return ['code' => 0, 'msg' => '密码修改失败'];
    }
}