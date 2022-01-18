<?php
/**
 * 后台操作日志中间件
 */
namespace app\middleware\admin;

use app\model\admin\action\Log;
use laytp\BaseMiddleware;
use app\model\admin\Menu;
use app\service\admin\UserServiceFacade;
use think\Request;

class ActionLog extends BaseMiddleware
{
    /**
     * 执行中间件
     * @param Request $request
     * @param \Closure $next
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function handle(Request $request, \Closure $next)
    {
        $initUser = UserServiceFacade::init($request->header('laytp-admin-token', $request->cookie('laytpAdminToken')));
        if (!$initUser || $request->isGet()) return $next($request);

        $requestBody = $request->post();
        //日志不记录密码
        if (isset($requestBody['password'])) {
            $requestBody['password'] = '******';
        }

        $rule  = $request->url();
        $menu  = '';
        $menus = Menu::where('rule', '=', $rule)->order('pid', 'asc')->select()->toArray();
        if ($menus) {
            for ($i = 1; $i <= 4; $i++) {
                if ($menus[0]['pid']) {
                    $tempMenu = Menu::where('id', '=', $menus[0]['pid'])->find();
                    if ($tempMenu) {
                        $tempMenu = $tempMenu->toArray();
                        array_unshift($menus, $tempMenu);
                    }
                } else {
                    break;
                }
            }
            $menuName = [];
            foreach ($menus as $v) {
                $menuName[] = $v['name'];
            }
            $menu = implode(' - ', $menuName);
        }

        Log::create([
            'admin_id'       => UserServiceFacade::getUser()->id,
            'rule'           => $request->url(),
            'menu'           => $menu,
            'request_body'   => json_encode($requestBody),
            'request_header' => json_encode(request()->header()),
            'ip'             => request()->ip(),
            'create_time'    => date('Y-m-d H:i:s'),
        ]);

        return $next($request);
    }
}