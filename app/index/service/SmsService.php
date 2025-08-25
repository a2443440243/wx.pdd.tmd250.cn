<?php

namespace app\index\service;

use think\facade\Cache;
use think\facade\Log;
use think\facade\Config;

/**
 * 短信服务类
 * Class SmsService
 * @package app\index\service
 */
class SmsService
{
    /**
     * 验证码长度
     */
    const CODE_LENGTH = 6;
    
    /**
     * 验证码有效期（秒）
     */
    const CODE_EXPIRE = 300; // 5分钟
    
    /**
     * 发送间隔限制（秒）
     */
    const SEND_INTERVAL = 60; // 1分钟
    
    /**
     * 每日发送限制
     */
    const DAILY_LIMIT = 10;
    
    /**
     * 发送短信验证码
     * @param string $phone 手机号
     * @param string $type 类型：register注册，login登录，reset重置密码
     * @return array
     */
    public static function sendSmsCode(string $phone, string $type = 'register'): array
    {
        try {
            // 验证手机号格式
            if (!self::validatePhone($phone)) {
                return ['code' => 0, 'msg' => '手机号格式不正确'];
            }
            
            // 检查发送频率限制
            $intervalKey = "sms_interval_{$phone}";
            if (Cache::has($intervalKey)) {
                $remaining = Cache::get($intervalKey) - time();
                return ['code' => 0, 'msg' => "请{$remaining}秒后再试"];
            }
            
            // 检查每日发送限制
            $dailyKey = "sms_daily_{$phone}_" . date('Ymd');
            $dailyCount = Cache::get($dailyKey, 0);
            if ($dailyCount >= self::DAILY_LIMIT) {
                return ['code' => 0, 'msg' => '今日发送次数已达上限'];
            }
            
            // 生成验证码
            $code = self::generateCode();
            
            // 保存验证码到缓存
            $codeKey = "sms_code_{$phone}_{$type}";
            Cache::set($codeKey, $code, self::CODE_EXPIRE);
            
            // 发送短信（这里需要集成具体的短信服务商）
            $result = self::sendSms($phone, $code, $type);
            
            if ($result['success']) {
                // 设置发送间隔限制
                Cache::set($intervalKey, time() + self::SEND_INTERVAL, self::SEND_INTERVAL);
                
                // 增加每日发送计数
                Cache::set($dailyKey, $dailyCount + 1, 86400);
                
                // 记录发送日志
                Log::info("SMS sent to {$phone}, type: {$type}, code: {$code}");
                
                return ['code' => 1, 'msg' => '验证码发送成功'];
            } else {
                return ['code' => 0, 'msg' => $result['message'] ?? '发送失败'];
            }
            
        } catch (\Exception $e) {
            Log::error("SMS send error: " . $e->getMessage());
            return ['code' => 0, 'msg' => '发送失败，请稍后重试'];
        }
    }
    
    /**
     * 验证短信验证码
     * @param string $phone 手机号
     * @param string $code 验证码
     * @param string $type 类型
     * @param bool $deleteAfterVerify 验证后是否删除
     * @return bool
     */
    public static function verifySmsCode(string $phone, string $code, string $type = 'register', bool $deleteAfterVerify = true): bool
    {
        try {
            $codeKey = "sms_code_{$phone}_{$type}";
            $cachedCode = Cache::get($codeKey);
            
            if (!$cachedCode) {
                return false; // 验证码不存在或已过期
            }
            
            $isValid = $cachedCode === $code;
            
            if ($isValid && $deleteAfterVerify) {
                // 验证成功后删除验证码
                Cache::delete($codeKey);
            }
            
            // 记录验证日志
            Log::info("SMS verify for {$phone}, type: {$type}, result: " . ($isValid ? 'success' : 'failed'));
            
            return $isValid;
            
        } catch (\Exception $e) {
            Log::error("SMS verify error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 生成验证码
     * @return string
     */
    private static function generateCode(): string
    {
        return str_pad(mt_rand(0, pow(10, self::CODE_LENGTH) - 1), self::CODE_LENGTH, '0', STR_PAD_LEFT);
    }
    
    /**
     * 验证手机号格式
     * @param string $phone
     * @return bool
     */
    private static function validatePhone(string $phone): bool
    {
        return preg_match('/^1[3-9]\d{9}$/', $phone);
    }
    
    /**
     * 发送短信（需要集成具体的短信服务商）
     * @param string $phone 手机号
     * @param string $code 验证码
     * @param string $type 类型
     * @return array
     */
    private static function sendSms(string $phone, string $code, string $type): array
    {
        // 这里需要集成具体的短信服务商API
        // 例如：阿里云短信、腾讯云短信、华为云短信等
        
        // 获取短信配置
        $smsConfig = Config::get('sms', []);
        
        // 根据类型选择短信模板
        $templates = [
            'register' => '您的注册验证码是：{code}，5分钟内有效，请勿泄露。',
            'login' => '您的登录验证码是：{code}，5分钟内有效，请勿泄露。',
            'reset' => '您的密码重置验证码是：{code}，5分钟内有效，请勿泄露。'
        ];
        
        $template = $templates[$type] ?? $templates['register'];
        $message = str_replace('{code}', $code, $template);
        
        // 开发环境下直接返回成功（用于测试）
        if (app()->isDebug()) {
            Log::info("[DEBUG] SMS to {$phone}: {$message}");
            return ['success' => true, 'message' => 'Debug mode: SMS sent'];
        }
        
        // 生产环境需要调用真实的短信API
        // 示例代码（需要根据实际短信服务商调整）:
        /*
        try {
            // 调用短信服务商API
            $client = new SmsClient($smsConfig);
            $result = $client->send($phone, $message);
            
            if ($result->isSuccess()) {
                return ['success' => true, 'message' => 'SMS sent successfully'];
            } else {
                return ['success' => false, 'message' => $result->getErrorMessage()];
            }
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
        */
        
        // 临时返回成功（实际项目中需要替换为真实的短信发送逻辑）
        return ['success' => true, 'message' => 'SMS sent (mock)'];
    }
    
    /**
     * 清理过期的验证码缓存
     * @return int 清理的数量
     */
    public static function cleanExpiredCodes(): int
    {
        // 这个方法可以通过定时任务调用，清理过期的缓存
        // 具体实现取决于使用的缓存驱动
        return 0;
    }
    
    /**
     * 获取手机号今日剩余发送次数
     * @param string $phone
     * @return int
     */
    public static function getRemainingCount(string $phone): int
    {
        $dailyKey = "sms_daily_{$phone}_" . date('Ymd');
        $dailyCount = Cache::get($dailyKey, 0);
        return max(0, self::DAILY_LIMIT - $dailyCount);
    }
    
    /**
     * 检查是否可以发送短信
     * @param string $phone
     * @return array
     */
    public static function canSendSms(string $phone): array
    {
        // 检查发送间隔
        $intervalKey = "sms_interval_{$phone}";
        if (Cache::has($intervalKey)) {
            $remaining = Cache::get($intervalKey) - time();
            return [
                'can_send' => false,
                'reason' => 'interval_limit',
                'remaining_time' => $remaining
            ];
        }
        
        // 检查每日限制
        if (self::getRemainingCount($phone) <= 0) {
            return [
                'can_send' => false,
                'reason' => 'daily_limit',
                'remaining_time' => 0
            ];
        }
        
        return ['can_send' => true];
    }
}