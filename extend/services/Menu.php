<?php

namespace services;

/**
 * 菜单类
 */
class Menu extends Services
{
    public $default_icon = 'layui-icon layui-icon-star-fill';
    public $menu_model;

    public function __construct(){
        $this->menu_model = new \app\admin\model\auth\Menu();
    }

    //创建菜单
    public function create($menus,$pid=0){
        foreach($menus as $menu){
            $id = \app\admin\model\auth\Menu::where('rule','=',$menu['rule'])->value('id');
            if(!$id){
                $add_menu = [
                    'name' => $menu['name'],
                    'rule' => $menu['rule'],
                    'is_menu' => $menu['is_menu'],
                    'pid' => $pid,
                    'icon' => isset($menu['icon']) ? $menu['icon'] : $this->default_icon
                ];
                $id = $this->menu_model->insertGetId($add_menu);
            }

            if(isset($menu['children'])){
                self::create($menu['children'],$id);
            }
        }
        return true;
    }

    //删除菜单
    public function delete(){

    }

    //启用菜单
    public function enable(){

    }

    //禁用菜单
    public function disable(){

    }
}