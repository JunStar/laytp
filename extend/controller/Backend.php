<?php
/**
 * 后台基类
 */

namespace controller;

use think\Controller;
use think\facade\Hook;
use think\facade\Session;

class Backend extends Controller
{
    use \library\traits\Backend;
    public $module;
    public $controller;
    public $action;
    public $now_node;
    public $admin_user;
    public $rule_list;
    public $menu_ids;

    public function initialize(){
        if( $this->request->isPost() ){
            Hook::exec('app\\admin\\behavior\\AdminLog');
        }

        $this->module = $this->request->module();
        $this->controller = strtolower($this->request->controller());
        $this->action = strtolower($this->request->action());
        $this->now_node = $this->module . '/' . $this->controller . '/' . $this->action;

        $this->auth();
        $this->js_global_var();
        $this->menu();
    }

    //权限检测
    public function auth(){
        //当前登录用户信息
        $admin_user_id = Session::get('admin_user_id');
        if(!$admin_user_id){
            $this->redirect(url('/admin/auth.login/index'));
        }
        $this->admin_user = model('auth.User')->get($admin_user_id);
        $this->assign('admin_user', $this->admin_user);

        if($this->admin_user->is_super_manager){
            return true;
        }

        //当前登录用户角色
        $role_ids = model('auth.RoleRelUser')->where('admin_id','=',$admin_user_id)->column('role_id');
        $menu_ids = array_unique( model('auth.RoleRelMenu')->where('role_id','in',$role_ids)->column('menu_id') );
        foreach($menu_ids as $menu_id){
            $pid = model('auth.Menu')->where('id','=',$menu_id)->value('pid');
            if($pid){
                for($i=1;$i<=3;$i++){
                    $menu_info = model('auth.Menu')->where('id','=',$pid)->find();
                    $menu_ids[] = $menu_info['id'];
                    if(!$menu_info['pid']){break;}
                    $pid = $menu_info['pid'];
                }
            }
        }

        $this->menu_ids = array_unique( $menu_ids );
        $where = [
            ['id','in',$menu_ids]
        ];
        $rule_list = array_unique( model('auth.Menu')->where($where)->column('rule') );
        $this->rule_list = $rule_list;
        $this->assign('rule_list', $this->rule_list);

        if( !in_array($this->now_node, $rule_list) ){
            $this->error('无权访问');
        }
    }

    //设置菜单
    public function menu(){
        //当前菜单信息
        $now_node_where['rule'] = $this->now_node;
        $now_node_where['is_menu'] = 1;
        $now_menus = model('auth.Menu')->where($now_node_where)->order('pid desc')->select()->toArray();
        if( !$now_menus ){
            $first_menu = model('auth.Menu')->where('pid','=',0)->select()->toArray();
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
        $now_second_menu = model('auth.Menu')->where('id','=',$now_menu['pid'])->find();
        if($now_second_menu && $now_second_menu['pid']){
            $now_second_menu = $now_second_menu->toArray();
        }else{
            $now_second_menu = $now_menu;
        }
        //当前一级菜单信息
        $now_first_menu = model('auth.Menu')->where('id','=',$now_second_menu['pid'])->find();
        if($now_first_menu){
            $now_first_menu = $now_first_menu->toArray();
        }else{
            $now_first_menu = $now_second_menu;
        }
        //获取所有一级菜单
        $first_menu_where[] = ['pid','=',0];
        if( !$this->admin_user->is_super_manager ){
            $first_menu_where[] =['id','in',$this->menu_ids];
        }
        $first_menu = model('auth.Menu')->where($first_menu_where)->order('sort','desc')->select()->toArray();
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
        $second_menu_where[] = ['pid','=',$now_first_menu['id']];
        if( !$this->admin_user->is_super_manager ){
            $second_menu_where[] =['id','in',$this->menu_ids];
        }
        $second_menu = model('auth.Menu')->where($second_menu_where)->order('sort','desc')->select()->toArray();
        foreach($second_menu as $sk=>$sv){
            //设置选中的二级菜单
            if($sv['id'] == $now_second_menu['id']){
                $second_menu[$sk]['selected'] = true;
            }else{
                $second_menu[$sk]['selected'] = false;
            }
            $third_menu_where = [];
            $third_menu_where[] = ['pid','=',$sv['id']];
            if( !$this->admin_user->is_super_manager ){
                $third_menu_where[] =['id','in',$this->menu_ids];
            }
            $third_menu = model('auth.Menu')->where($third_menu_where)->order('sort','desc')->select()->toArray();
            if( count($third_menu) ){
                foreach($third_menu as $tk=>$tv){
                    //设置选中的三级菜单
                    if( $tv['rule'] == $this->now_node ){
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
        $assign['js_global_var']['table_id'] = $module . $controller . $action;

        $this->assign($assign);
    }

    /**
     * 生成查询条件
     * @return array
     */
    public function build_params(){
        $where = [];
        $search_param = $this->request->param('search_param');
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

    public function build_select_page_params(){
        $where = [];
        $q_word = $this->request->param('q_word');
        $andOr = $this->request->param('andOr');
        $searchField = $this->request->param('searchField');
        $searchFieldStr = $searchField[0];
        $searchFieldVal = $this->request->param($searchFieldStr);
        foreach($q_word as $keyword){
            $where[] = "{$searchFieldStr} LIKE '%{$keyword}%'";
        }
        $whereStr = implode(" {$andOr} ", $where);
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