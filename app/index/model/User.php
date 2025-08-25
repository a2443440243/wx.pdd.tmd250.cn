<?php

// +----------------------------------------------------------------------
// | Index Module User Model for ThinkAdmin
// +----------------------------------------------------------------------
// | 版权所有 2014~2024 ThinkAdmin [ thinkadmin.top ]
// +----------------------------------------------------------------------
// | 官方网站: https://thinkadmin.top
// +----------------------------------------------------------------------
// | 开源协议 ( https://mit-license.org )
// | 免责声明 ( https://thinkadmin.top/disclaimer )
// +----------------------------------------------------------------------

namespace app\index\model;

/**
 * 前台用户模型
 * Class User
 * @package app\index\model
 */
class User extends BaseModel
{
    /**
     * 数据表名
     * @var string
     */
    protected $name = 'index_user';

    /**
     * 自动时间戳
     * @var bool
     */
    protected $autoWriteTimestamp = true;

    /**
     * 字段类型转换
     * @var array
     */
    protected $type = [
        'login_time' => 'datetime',
        'create_time' => 'datetime',
        'update_time' => 'datetime',
    ];

    /**
     * 隐藏字段
     * @var array
     */
    protected $hidden = ['password', 'deleted'];

    /**
     * 只读字段
     * @var array
     */
    protected $readonly = ['phone', 'create_time'];

    /**
     * 密码修改器
     * @param string $value
     * @return string
     */
    public function setPasswordAttr(string $value): string
    {
        return password_hash($value, PASSWORD_DEFAULT);
    }

    /**
     * 头像获取器
     * @param string|null $value
     * @return string
     */
    public function getAvatarAttr(?string $value): string
    {
        return empty($value) ? '/static/theme/img/default-avatar.png' : $value;
    }

    /**
     * 性别获取器
     * @param int $value
     * @return string
     */
    public function getGenderTextAttr(int $value): string
    {
        $genders = [0 => '未知', 1 => '男', 2 => '女'];
        return $genders[$value] ?? '未知';
    }

    /**
     * 手机号登录
     * @param string $phone
     * @param string $password
     * @return array
     */
    public static function login(string $phone, string $password): array
    {
        $user = static::where('phone', $phone)
            ->where('status', 1)
            ->where('deleted', 0)
            ->find();

        if (empty($user)) {
            return ['code' => 0, 'msg' => '用户不存在或已被禁用'];
        }

        if (!password_verify($password, $user['password'])) {
            return ['code' => 0, 'msg' => '密码错误'];
        }

        // 更新登录时间
        $user->save(['login_time' => date('Y-m-d H:i:s')]);

        return ['code' => 1, 'msg' => '登录成功', 'data' => $user->hidden(['password'])->toArray()];
    }

    /**
     * 手机号注册
     * @param string $phone
     * @param string $password
     * @param string $nickname
     * @return array
     */
    public static function register(string $phone, string $password, string $nickname = ''): array
    {
        // 检查手机号是否已存在
        $exists = static::where('phone', $phone)->where('deleted', 0)->find();
        if ($exists) {
            return ['code' => 0, 'msg' => '手机号已被注册'];
        }

        // 创建用户
        $user = static::create([
            'phone' => $phone,
            'password' => $password,
            'nickname' => $nickname ?: '用户' . substr($phone, -4),
            'status' => 1,
            'deleted' => 0
        ]);

        if ($user) {
            return ['code' => 1, 'msg' => '注册成功', 'data' => $user->hidden(['password'])->toArray()];
        }

        return ['code' => 0, 'msg' => '注册失败'];
    }

    /**
     * 验证手机号格式
     * @param string $phone
     * @return bool
     */
    public static function validatePhone(string $phone): bool
    {
        return preg_match('/^1[3-9]\d{9}$/', $phone);
    }
}