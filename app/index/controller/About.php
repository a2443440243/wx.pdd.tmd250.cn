<?php

// +----------------------------------------------------------------------
// | Index Module About Controller for ThinkAdmin
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

/**
 * 关于我们控制器
 * Class About
 * @package app\index\controller
 */
class About extends Controller
{
    /**
     * 关于我们页面
     * @return string|null
     */
    public function index()
    {
        // 设置页面信息
        $this->assign([
            'title'       => '关于我们',
            'keywords'    => 'ThinkAdmin,关于我们,公司介绍',
            'description' => 'ThinkAdmin 专注于为企业提供优质的技术解决方案',
        ]);
        
        return $this->fetch();
    }
    
    /**
     * 详情页面
     * @param int $id
     * @return string|null
     */
    public function detail(int $id = 0)
    {
        if (empty($id)) {
            $this->error('参数错误');
        }
        
        // 这里可以根据 ID 获取具体内容
        $detail = [
            'id'          => $id,
            'title'       => '公司详情',
            'content'     => '这里是详细内容...',
            'create_time' => date('Y-m-d H:i:s'),
        ];
        
        $this->assign([
            'title'  => $detail['title'],
            'detail' => $detail,
        ]);
        
        return $this->fetch();
    }
}