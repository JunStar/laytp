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
    public function delete($menus){
        foreach($menus as $menu){
            $info = \app\admin\model\auth\Menu::where('rule','=',$menu['rule'])
                ->where('name','=',$menu['name'])
                ->find();
            if($info) {
                if ($menu['delete_status'] == 1 && !$info->is_hide) {
                    $info->is_hide = 1;
                    $info->save();
                }
            }

            if(isset($menu['children'])){
                self::delete($menu['children']);
            }
        }
        return true;
    }

    //启用菜单
    public function enable($menus){
        foreach($menus as $menu){
            $info = \app\admin\model\auth\Menu::where('rule','=',$menu['rule'])
                ->where('name','=',$menu['name'])
                ->find();
            if(is_object($info)){
                if($info->is_hide) {
                    $info->is_hide = 0;
                    $info->save();
                }
            }

            if(isset($menu['children'])){
                self::enable($menu['children']);
            }
        }
        return true;
    }

    //禁用菜单
    public function disable($menus){
        foreach($menus as $menu){
            $info = \app\admin\model\auth\Menu::where('rule','=',$menu['rule'])
                ->where('name','=',$menu['name'])
                ->find();
            if($info) {
                if ($menu['delete_status'] == 1 && !$info->is_hide) {
                    $info->is_hide = 1;
                    $info->save();
                }
            }

            if(isset($menu['children'])){
                self::disable($menu['children']);
            }
        }
        return true;
    }
}