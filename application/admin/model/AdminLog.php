<?php

namespace app\admin\model;

use think\Model;

class AdminLog extends Model
{

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'datetime';
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = '';
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
        $name = $admin_user_id ? $admin_user->name : '';
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
            $menus = model('auth.Menu')->where('rule','=',$now_node)->order('pid','asc')->column('name');
            $title = implode(' - ', $menus);
        }

        self::create([
            'title'     => $title,
            'content'   => !is_scalar($content) ? json_encode($content) : $content,
            'url'       => request()->url(),
            'admin_id'  => $admin_id,
            'name'      => $name,
            'user_agent' => request()->server('HTTP_USER_AGENT'),
            'ip'        => request()->ip()
        ]);
    }

    public function admin()
    {
        return $this->belongsTo('Admin', 'admin_id')->setEagerlyType(0);
    }

}
