<?php
namespace app\admin\service\addons;

use service\Service;
/**
 * 菜单类
 */
class Menu extends Service
{
    public $default_icon = 'layui-icon layui-icon-fire';
    public $default_rule = 'default';
    public $menu_model;

    public function __construct(){
        $this->menu_model = new \app\admin\model\auth\Menu();
    }

    //创建菜单
    public function create($menus,$pid=0){
        static $ids;
        foreach($menus as $menu){
            $add_menu = [
                'name' => $menu['name'],
                'des' => isset($menu['des']) ? $menu['des'] : '',
                'rule' => isset($menu['rule']) ? $menu['rule'] : $this->default_rule,
                'is_menu' => $menu['is_menu'],
                'pid' => $pid,
                'icon' => isset($menu['icon']) ? $menu['icon'] : $this->default_icon
            ];
            $id = $this->menu_model->insertGetId($add_menu);
            $ids[] = $id;

            if(isset($menu['children'])){
                self::create($menu['children'],$id);
            }
        }
        return $ids;
    }

    //删除菜单
    public function delete($menu_ids){
        \app\admin\model\auth\Menu::where('id','in',$menu_ids)->delete();
        return true;
    }

    //启用菜单
    public function enable($menu_ids){
        \app\admin\model\auth\Menu::where('id','in',$menu_ids)->update(['is_hide'=>0]);
        return true;
    }

    //禁用菜单
    public function disable($menu_ids){
        \app\admin\model\auth\Menu::where('id','in',$menu_ids)->update(['is_hide'=>1]);
        return true;
    }
}