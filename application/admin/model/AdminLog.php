<?php

namespace app\admin\model;

use model\Backend;

class AdminLog extends Backend
{
    //自定义日志标题
    protected static $title = '';
    //自定义日志内容
    protected static $content = '';

    public static function setTitle($title)
    {
        self::$title = $title;
    }

    public static function setContent($content)
    {
        self::$content = $content;
    }

    public static function record($title = '')
    {
        $admin_user_id = isLogin();
        $admin_user = model('auth.User')->get($admin_user_id);

        $admin_id = $admin_user_id ? $admin_user->id : 0;
        $content = self::$content;
        if (!$content)
        {
            $content = request()->param();
            foreach ($content as $k => $v)
            {
                if (is_string($v) && strlen($v) > 200 || stripos($k, 'password') !== false)
                {
                    unset($content[$k]);
                }
            }
        }

        $module = request()->module();
        $controller = strtolower(request()->controller());
        $action = strtolower(request()->action());
        $now_node = $module . '/' . $controller . '/' . $action;

        $title = self::$title;
        if(!$title)
        {
            $menus = model('auth.Menu')->where('rule','=',$now_node)->order('pid','asc')->select()->toArray();
            if(!$menus){ return true; }
            for($i=1;$i<=4;$i++){
                if($menus[0]['pid']){
                    $new_menu = model('auth.Menu')->where('id','=',$menus[0]['pid'])->find()->toArray();
                    if($new_menu){
                        $new_menu = $new_menu->toArray();
                        array_unshift($menus, $new_menu );
                    }
                }else{
                    break;
                }
            }
            $menu_name = [];
            foreach($menus as $v){
                $menu_name[] = $v['name'];
            }
            $title = implode(' - ', $menu_name);
        }

        self::create([
            'title'     => $title,
            'content'   => !is_scalar($content) ? json_encode($content) : $content,
            'url'       => request()->url(),
            'admin_id'  => $admin_id,
            'user_agent' => request()->server('HTTP_USER_AGENT'),
            'ip'        => request()->ip()
        ]);
    }

    public function admin()
    {
        return $this->belongsTo('Admin', 'admin_id')->setEagerlyType(0);
    }

}
