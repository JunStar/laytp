<?php
/**
 * 后台基类
 */

namespace controller;

use think\Controller;
use think\facade\Session;

class Backend extends Controller
{
    use \library\traits\Backend;
    public $module;
    public $controller;
    public $action;

    public function initialize(){
        $this->auth();

        $this->module = $this->request->module();
        $this->controller = strtolower($this->request->controller());
        $this->action = strtolower($this->request->action());

        $this->js_global_var();
        $this->menu();
    }

    //权限检测
    public function auth(){
        $admin_user = Session::get('admin_user');
    }

    //设置菜单
    public function menu(){
        //当前节点
        $now_node = $this->module . '/' . $this->controller . '/' . $this->action;
        //当前菜单信息
        $now_node_where['rule'] = $now_node;
        $now_menus = model('Menu')->where($now_node_where)->order('pid desc')->select()->toArray();
        if( !$now_menus ){
            $first_menu = model('Menu')->where('pid','=',0)->select()->toArray();
            foreach($first_menu as $k=>$v){
                $first_menu[$k]['selected'] = false;
            }
            $this->assign('first_menu', $first_menu);
            $this->assign('left_menu', []);
            return true;
        }else{
            $now_menu = $now_menus[0];
        }
        //当前二级菜单信息
        $now_second_menu = model('Menu')->where('id','=',$now_menu['pid'])->find();
        if($now_second_menu && $now_second_menu['pid']){
            $now_second_menu = $now_second_menu->toArray();
        }else{
            $now_second_menu = $now_menu;
        }
        //当前一级菜单信息
        $now_first_menu = model('Menu')->where('id','=',$now_second_menu['pid'])->find();
        if($now_first_menu){
            $now_first_menu = $now_first_menu->toArray();
        }else{
            $now_first_menu = $now_second_menu;
        }
        //获取所有一级菜单
        $first_menu = model('Menu')->where('pid','=',0)->select()->toArray();
        foreach($first_menu as $k=>$v){
            //设置选中的一级菜单
            if($v['id'] == $now_first_menu['id']){
                $first_menu[$k]['selected'] = true;
            }else{
                $first_menu[$k]['selected'] = false;
            }
        }
        $this->assign('first_menu', $first_menu);
        //获取当前一级菜单下的二级和三级菜单
        $second_menu = model('Menu')->where('pid','=',$now_first_menu['id'])->order('sort desc')->select()->toArray();
        foreach($second_menu as $sk=>$sv){
            //设置选中的二级菜单
            if($sv['id'] == $now_second_menu['id']){
                $second_menu[$sk]['selected'] = true;
            }else{
                $second_menu[$sk]['selected'] = false;
            }
            $third_menu = model('Menu')->where('pid','=',$sv['id'])->order('sort desc')->select()->toArray();
            if( count($third_menu) ){
                foreach($third_menu as $tk=>$tv){
                    //设置选中的三级菜单
                    if( $tv['rule'] == $now_node ){
                        $third_menu[$tk]['selected'] = true;
                    }else{
                        $third_menu[$tk]['selected'] = false;
                    }
                }
                $second_menu[$sk]['child'] = $third_menu;
            }else{
                $second_menu[$sk]['child'] = [];
            }
        }
        $this->assign('left_menu', $second_menu);
    }

    /**
     * 渲染js全局变量
     */
    public function js_global_var(){
        $module = $this->request->module();
        $controller = strtolower($this->request->controller());
        $action = strtolower($this->request->action());

        $assign['js_global_var']['module'] = $module;
        $assign['js_global_var']['controller'] = $controller;
        $assign['js_global_var']['js_controller'] = str_replace('.','/',$controller);
        $assign['js_global_var']['action'] = $action;
        $this->assign($assign);
    }

    /**
     * 生成查询条件
     * @return array
     */
    public function build_params(){
        $where = [];
        $search_param = $this->request->post('search_param');
        if( $search_param ){
            foreach($search_param as $field=>$value_condition){
                if($value_condition['value'] != ''){
                    $condition = strtoupper($value_condition['condition']);
                    switch( $condition ){
                        case '=':
                            $where[] = "$field = {$value_condition['value']}";
                            break;
                        case 'FIND_IN_SET':
                            $values = explode(',', $value_condition['value']);
                            $find_in_set = [];
                            foreach($values as $val){
                                $find_in_set[] = "find_in_set({$val},{$field})";
                            }
                            $where[] = '(' . implode(' OR ', $find_in_set) . ')';
                            break;
                        case 'LIKE':
                            $where[] = "$field LIKE '%{$value_condition['value']}%'";
                            break;
                        case 'IN':
                            $where[] = "$field IN ({$value_condition['value']})";
                            break;
                        case 'BETWEEN':
                            $arr_between = explode(' - ', $value_condition['value']);
                            $where[] = "($field BETWEEN '{$arr_between[0]}' and '{$arr_between[1]}')";
                            break;
                    }
                }
            }
        }
        $whereStr = implode(' AND ', $where);
        return $whereStr;
    }

    //重写tp基类的success方法，修改下data和url参数的位置
    public function success($msg = '', $data = '', $url = null, $wait = 3, array $header = []){
        return parent::success($msg, $url, $data, $wait, $header);
    }

    //重写tp基类的error方法，修改下data和url参数的位置
    public function error($msg = '', $data = '', $url = null, $wait = 3, array $header = []){
        return parent::error($msg, $url, $data, $wait, $header);
    }
}