<?php

namespace app\index\controller;

use think\admin\Controller;
use think\admin\helper\QueryHelper;
use think\admin\model\SystemUser;
use think\admin\service\AdminService;
use think\Request;
use think\facade\Db;
use app\index\service\AuthService;

/**
 * 个人中心管理
 * @class Profile
 * @package app\index\controller
 */
class Profile extends Controller
{
    /**
     * 个人中心首页
     * @auth false
     * @menu false
     */
    public function index()
    {
        $this->title = '个人中心';
        $this->assign('user', $this->getCurrentUser());
        return $this->fetch();
    }

    /**
     * 个人信息管理
     */
    public function info()
    {
        $this->title = '个人信息';
        $user = $this->getCurrentUser();
        $this->assign('user', $user);
        return $this->fetch();
    }
    
    /**
     * 更新个人信息
     */
    public function updateInfo()
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            $user = $this->getCurrentUser();
            
            // 验证数据
            $validate = [
                'nickname' => 'max:50',
                'contact_mail' => 'email',
                'contact_phone' => 'mobile',
                'contact_qq' => 'number',
                'desc' => 'max:500'
            ];
            
            $this->validate($data, $validate);
            
            // 更新用户信息
            $result = Db::name('index_user')->where('id', $user['id'])->update([
                'nickname' => $data['nickname'] ?? '',
                'address' => $data['address'] ?? '',
                'update_time' => date('Y-m-d H:i:s')
            ]);
            
            if ($result !== false) {
                $this->success('个人信息更新成功');
            } else {
                $this->error('个人信息更新失败');
            }
        }
    }

    /**
     * 修改密码
     */
    public function password()
    {
        $this->title = '修改密码';
        return $this->fetch();
    }
    
    /**
     * 更新密码
     */
    public function updatePassword()
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            $user = $this->getCurrentUser();
            
            // 验证数据
            if (empty($data['old_password'])) {
                $this->error('请输入当前密码');
            }
            if (empty($data['password'])) {
                $this->error('请输入新密码');
            }
            if (strlen($data['password']) < 6) {
                $this->error('新密码长度不能少于6位');
            }
            if ($data['password'] !== $data['repassword']) {
                $this->error('两次密码输入不一致');
            }
            
            // 验证当前密码
            if (!password_verify($data['old_password'], $user['password'])) {
                $this->error('当前密码错误');
            }
            
            // 更新密码
            $newPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            $result = Db::name('index_user')->where('id', $user['id'])->update([
                'password' => $newPassword,
                'update_time' => date('Y-m-d H:i:s')
            ]);
            
            if ($result !== false) {
                $this->success('密码修改成功');
            } else {
                $this->error('密码修改失败');
            }
        }
    }

    /**
     * 头像设置
     */
    public function avatar()
    {
        $this->title = '头像设置';
        $user = $this->getCurrentUser();
        $this->assign('user', $user);
        return $this->fetch();
    }
    
    /**
     * 上传头像
     */
    public function uploadAvatar()
    {
        if ($this->request->isPost()) {
            $file = $this->request->file('file');
            if (!$file) {
                $this->error('请选择要上传的文件');
            }
            
            // 验证文件
            $validate = [
                'size' => 2 * 1024 * 1024, // 2MB
                'ext' => 'jpg,jpeg,png'
            ];
            
            $info = $file->validate($validate)->move(root_path() . 'public/upload/avatar/');
            if ($info) {
                $user = $this->getCurrentUser();
                $avatarPath = '/upload/avatar/' . $info->getSaveName();
                
                // 更新用户头像
                $result = Db::name('index_user')->where('id', $user['id'])->update([
                    'avatar' => $avatarPath,
                    'update_time' => date('Y-m-d H:i:s')
                ]);
                
                if ($result !== false) {
                    $this->success('头像上传成功', '', ['url' => $avatarPath]);
                } else {
                    $this->error('头像保存失败');
                }
            } else {
                $this->error($file->getError());
            }
        }
    }

    /**
     * 登录记录
     */
    public function loginlog()
    {
        $this->title = '登录记录';
        $user = $this->getCurrentUser();
        
        // 统计信息
        $stats = [
            'total_login' => $user['login_num'] ?? 0,
            'today_login' => Db::name('index_user_log')
                ->where('user_id', $user['id'])
                ->where('action', 'like', '%登录%')
                ->whereTime('create_time', 'today')
                ->count(),
            'week_login' => Db::name('index_user_log')
                ->where('user_id', $user['id'])
                ->where('action', 'like', '%登录%')
                ->whereTime('create_time', 'week')
                ->count(),
            'last_login' => $user['login_at'] ?? '从未登录'
        ];
        
        $this->assign('stats', $stats);
        return $this->fetch();
    }
    
    /**
     * 获取登录记录
     */
    public function getLoginLog()
    {
        $user = $this->getCurrentUser();
        $page = $this->request->param('page', 1);
        $limit = $this->request->param('limit', 20);
        $where = [];
        
        // 搜索条件
        if ($ip = $this->request->param('ip')) {
            $where[] = ['node', 'like', '%' . $ip . '%'];
        }
        if ($location = $this->request->param('location')) {
            $where[] = ['content', 'like', '%' . $location . '%'];
        }
        if ($startDate = $this->request->param('start_date')) {
            $where[] = ['create_time', '>=', $startDate . ' 00:00:00'];
        }
        if ($endDate = $this->request->param('end_date')) {
            $where[] = ['create_time', '<=', $endDate . ' 23:59:59'];
        }
        
        $where[] = ['phone', '=', $user['phone']];
        $where[] = ['action', '=', 'login'];
        
        $query = Db::name('index_user_log')->where($where);
        $total = $query->count();
        $list = $query->order('id desc')
            ->page($page, $limit)
            ->select()
            ->toArray();
        
        // 处理数据
        foreach ($list as &$item) {
            $item['login_ip'] = $this->extractIpFromNode($item['node']);
            $item['login_location'] = $this->getLocationFromContent($item['content']);
            $item['user_agent'] = $this->getUserAgentFromContent($item['content']);
            $item['device_type'] = $this->getDeviceType($item['content']);
            $item['status'] = 1; // 登录成功
            $item['login_time'] = $item['create_time'];
            $item['remark'] = $item['content'];
        }
        
        return json([
            'code' => 0,
            'msg' => '',
            'count' => $total,
            'data' => $list
        ]);
    }

    /**
     * 获取当前用户信息
     * @return array
     */
    private function getCurrentUser()
    {
        // 使用AuthService获取当前用户
        $user = AuthService::getCurrentUser();
        if (empty($user)) {
            $this->redirect('/index/auth/login');
        }
        
        return $user;
    }
    
    /**
     * 从节点信息中提取IP地址
     * @param string $node
     * @return string
     */
    private function extractIpFromNode($node)
    {
        if (preg_match('/\b(?:[0-9]{1,3}\.){3}[0-9]{1,3}\b/', $node, $matches)) {
            return $matches[0];
        }
        return '未知';
    }
    
    /**
     * 从内容中获取地理位置
     * @param string $content
     * @return string
     */
    private function getLocationFromContent($content)
    {
        // 这里可以根据实际情况解析地理位置信息
        // 暂时返回默认值
        return '未知地区';
    }
    
    /**
     * 从内容中获取用户代理信息
     * @param string $content
     * @return string
     */
    private function getUserAgentFromContent($content)
    {
        // 这里可以根据实际情况解析用户代理信息
        if (strpos($content, 'Chrome') !== false) {
            return 'Chrome';
        } elseif (strpos($content, 'Firefox') !== false) {
            return 'Firefox';
        } elseif (strpos($content, 'Safari') !== false) {
            return 'Safari';
        } elseif (strpos($content, 'Edge') !== false) {
            return 'Edge';
        }
        return '未知浏览器';
    }
    
    /**
     * 从内容中获取设备类型
     * @param string $content
     * @return string
     */
    private function getDeviceType($content)
    {
        if (strpos($content, 'Mobile') !== false || strpos($content, 'Android') !== false || strpos($content, 'iPhone') !== false) {
            return 'mobile';
        } elseif (strpos($content, 'Tablet') !== false || strpos($content, 'iPad') !== false) {
            return 'tablet';
        }
        return 'desktop';
    }
}