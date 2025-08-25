<?php

// +----------------------------------------------------------------------
// | Index Module Middleware for ThinkAdmin
// +----------------------------------------------------------------------
// | 版权所有 2014~2024 ThinkAdmin [ thinkadmin.top ]
// +----------------------------------------------------------------------
// | 官方网站: https://thinkadmin.top
// +----------------------------------------------------------------------
// | 开源协议 ( https://mit-license.org )
// | 免责声明 ( https://thinkadmin.top/disclaimer )
// +----------------------------------------------------------------------

namespace app\index\middleware;

use Closure;
use think\Request;
use think\Response;
use app\index\service\IndexService;

/**
 * 前台模块中间件
 * Class IndexMiddleware
 * @package app\index\middleware
 */
class IndexMiddleware
{
    /**
     * 处理请求
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 记录访问日志
        $this->logVisit($request);
        
        // 设置全局变量
        $this->setGlobalVars($request);
        
        // 检查维护模式 - 临时禁用
        // if ($this->isMaintenanceMode()) {
        //     return response('网站维护中，请稍后访问', 503, [
        //         'Content-Type' => 'text/html; charset=utf-8'
        //     ]);
        // }
        
        // 处理跨域请求
        $response = $next($request);
        
        // 设置响应头
        return $this->setResponseHeaders($response);
    }
    
    /**
     * 记录访问日志
     * @param Request $request
     */
    private function logVisit(Request $request): void
    {
        try {
            IndexService::logVisit([
                'module' => 'index',
                'controller' => $request->controller(),
                'action' => $request->action(),
            ]);
        } catch (\Exception $e) {
            // 记录日志失败不影响正常访问
            trace('访问日志记录失败: ' . $e->getMessage(), 'error');
        }
    }
    
    /**
     * 设置全局变量
     * @param Request $request
     */
    private function setGlobalVars(Request $request): void
    {
        // 获取网站配置
        $config = IndexService::getWebConfig();
        
        // 设置模板变量
        view()->assign([
            'site_name'        => $config['site_name'],
            'site_title'       => $config['site_title'],
            'site_keywords'    => $config['site_keywords'],
            'site_description' => $config['site_description'],
            'site_logo'        => $config['site_logo'],
            'site_icon'        => $config['site_icon'],
            'site_copyright'   => $config['site_copyright'],
            'nav_menu'         => IndexService::getNavMenu(),
            'current_url'      => $request->url(true),
            'request_time'     => date('Y-m-d H:i:s'),
        ]);
    }
    
    /**
     * 检查维护模式
     * @return bool
     */
    private function isMaintenanceMode(): bool
    {
        // 检查是否开启维护模式
        return (bool) sysconf('site_maintenance', 0);
    }
    
    /**
     * 设置响应头
     * @param Response $response
     * @return Response
     */
    private function setResponseHeaders(Response $response): Response
    {
        // 设置安全响应头
        $response->header([
            'X-Frame-Options' => 'SAMEORIGIN',
            'X-Content-Type-Options' => 'nosniff',
            'X-XSS-Protection' => '1; mode=block',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
        ]);
        
        // 设置缓存头（根据内容类型）
        $contentType = $response->getHeader('Content-Type');
        if (strpos($contentType, 'text/html') !== false) {
            // HTML 页面不缓存
            $response->header([
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ]);
        }
        
        return $response;
    }
}