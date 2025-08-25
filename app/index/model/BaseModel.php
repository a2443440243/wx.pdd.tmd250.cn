<?php

// +----------------------------------------------------------------------
// | Index Module Model for ThinkAdmin
// +----------------------------------------------------------------------
// | 版权所有 2014~2024 ThinkAdmin [ thinkadmin.top ]
// +----------------------------------------------------------------------
// | 官方网站: https://thinkadmin.top
// +----------------------------------------------------------------------
// | 开源协议 ( https://mit-license.org )
// | 免责声明 ( https://thinkadmin.top/disclaimer )
// +----------------------------------------------------------------------

namespace app\index\model;

use think\admin\Model;

/**
 * 前台基础模型
 * Class BaseModel
 * @package app\index\model
 */
abstract class BaseModel extends Model
{
    /**
     * 日志名称
     * @var string
     */
    protected $oplogName = '前台模块';

    /**
     * 日志类型
     * @var string
     */
    protected $oplogType = 'index';

    /**
     * 格式化创建时间
     * @param mixed $value
     * @return string
     */
    public function getCreateTimeAttr($value): string
    {
        return is_numeric($value) ? date('Y-m-d H:i:s', $value) : $value;
    }

    /**
     * 格式化更新时间
     * @param mixed $value
     * @return string
     */
    public function getUpdateTimeAttr($value): string
    {
        return is_numeric($value) ? date('Y-m-d H:i:s', $value) : $value;
    }

    /**
     * 获取状态文本
     * @param mixed $value
     * @return string
     */
    public function getStatusTextAttr($value): string
    {
        $status = $this->getAttr('status');
        return $status ? '启用' : '禁用';
    }

    /**
     * 软删除条件
     * @return array
     */
    public function scopeDeleted($query, $deleted = 0)
    {
        return $query->where('deleted', $deleted);
    }

    /**
     * 状态条件
     * @return array
     */
    public function scopeStatus($query, $status = 1)
    {
        return $query->where('status', $status);
    }
}