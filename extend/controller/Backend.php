<?php
/**
 * 后台控制器基类
 */

namespace controller;

use app\admin\model\auth\Menu;
use library\Token;
use library\Tree;
use think\Controller;
use think\facade\Config;
use think\facade\Cookie;
use think\facade\Hook;
use think\facade\Session;

class Backend extends Controller
{
    use \library\traits\Backend;
    public $module;//当前模型名
    public $controller;//当前控制器名
    public $action;//当前操作名
    public $now_node;//当前访问节点
    public $admin_user;//当前登录者信息
    public $role_ids;//当前登录者拥有的角色ID
    public $rule_list;//当前登录者拥有的权限节点列表
    public $menu_ids;//当前登录者拥有的菜单列表
    public $has_del=1;//当前访问的模型是否有删除功能
    public $has_soft_del=0;//当前访问的模型是否有软删除功能
    public $batch_action_list=['edit','del'];//批量操作下拉展示的节点函数名
    public $is_show_search_btn = true;//是否展示筛选按钮
    public $no_need_login=['select_page'];//无需登录的方法名（无需登录就不需要鉴权了）
    public $ref;//前端直接访问带[ref=菜单id]参数的链接地址就直接渲染菜单页，并且前端js把iframe的地址跳转到当前地址

    public function initialize(){
        if( $this->request->isPost() ){
            $token = $this->request->server('HTTP_TOKEN', $this->request->request('token', Cookie::get('token')));
            Hook::exec('app\\admin\\behavior\\AdminLog',$token);
        }

        $this->module = $this->request->module();
        $this->controller = strtolower($this->request->controller());
        $this->action = strtolower($this->request->action());
        $this->now_node = $this->module . '/' . $this->controller . '/' . $this->action;

        $this->auth();
        $this->init_assing_val();
        $this->is_show_batch();//是否显示批量操作
        $this->menu();
        if($this->ref){
            $default_menu = Menu::where('id',$this->ref)->find();
            if(!$default_menu){
                $this->error('菜单不存在');
            }
            $this->assign('default_menu', $default_menu);
            list($select_menu, $crumbs) = $this->set_crumbs($this->ref);
            array_shift($crumbs);
            $this->assign('crumbs', $crumbs);
            $this->assign('select_menu', $select_menu);

            //获取顶级菜单
            $this->assign('top_menu', $select_menu[count($select_menu)-1]);

            exit($this->fetch('admin@ltiframe/index'));
        }

        $menu_id = $this->request->param('laytp_menu_id');
        if($menu_id){
            $this->assign('menu_info', Menu::get($menu_id)->toArray());
        }else{
            $this->assign('menu_info', ['des'=>'']);
        }
    }

    //设置菜单
    public function set_crumbs($id){
        static $select_menu,$crumbs;
        foreach($this->menus as $k=>$v){
            if($v['id'] == $id){
                $select_menu[] = $v;
                $crumbs[] = $v['name'];
                $crumbs[] = '>';
                if($v['pid'] > 0){
                    $this->set_crumbs($v['pid']);
                }
                break;
            }
        }
        return [$select_menu, array_reverse($crumbs)];
    }

    //权限检测
    public function auth(){
        //当前登录用户信息
        $token = $this->request->server('HTTP_TOKEN', $this->request->request('token', Cookie::get('token')));
        if(!$token){
            $this->redirect(url('/admin/auth.login/index'));
        }
        $data = Token::get($token);
        if(!$data){
            $this->redirect(url('/admin/auth.login/index'));
        }
        $admin_user_id = $data['user_id'];
        if(!$admin_user_id){
            $this->redirect(url('/admin/auth.login/index'));
        }
        $this->admin_user = model('admin/auth.User')->get($admin_user_id);
        if(!$this->admin_user){
            Session::clear();
            $this->error('用户不存在');
        }
        $this->assign('laytp_admin_user', $this->admin_user);

        if($this->admin_user->is_super_manager){
            return true;
        }

        //当前登录用户角色
        $role_ids = model('admin/auth.RoleRelUser')->where('admin_id','=',$admin_user_id)->column('role_id');
        $this->role_ids = $role_ids;
        $menu_ids = array_unique( model('admin/auth.RoleRelMenu')->where('role_id','in',$role_ids)->column('menu_id') );
        foreach($menu_ids as $menu_id){
            $pid = model('admin/auth.Menu')->where('id','=',$menu_id)->value('pid');
            if($pid){
                for($i=1;$i<=3;$i++){
                    $menu_info = model('admin/auth.Menu')->where('id','=',$pid)->find();
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
        $rule_list = array_unique( model('admin/auth.Menu')->where($where)->column('rule') );
        $this->rule_list = $rule_list;

        if( !in_array($this->now_node, $rule_list) && !in_array($this->action, $this->no_need_login) ){
            $this->error('无权限访问，请联系管理员');
        }
    }

    //设置菜单
    public function menu(){
        $menus_where[] = ['is_menu','=',1];
        $menus_where[] = ['is_hide','=',0];
        if(!$this->admin_user->is_super_manager) $menus_where[] = ['id','in',$this->menu_ids];
        $menus = model('admin/auth.Menu')->where($menus_where)->order(['pid'=>'asc','sort'=>'desc'])->select()->toArray();
        $this->menus = $menus;
        $menu_tree_obj = Tree::instance();
        $menu_tree_obj->init($menus);
        $tree = $menu_tree_obj->getTreeArray(0);
        $assign['menu_tree'] = $tree;

        $first_menus = [];
        foreach($menus as $k=>$v){
            if($v['pid'] == 0){
                $v['default_menu'] = $menu_tree_obj->getDefaultMenu($v['id']);
                $v['select_menu_ids'] = implode(',',getSelectMenuIds($menu_tree_obj, $v['id'], true));
                $first_menus[] = $v;
            }
        }

        $first_menu_num = Config::get('laytp.basic.first_menu_num') ? Config::get('laytp.basic.first_menu_num') : 7;

        if(count($first_menus) > $first_menu_num){
            $assign['first_menus'] = array_slice($first_menus,0,$first_menu_num);
            $assign['more_first_menus'] = array_slice($first_menus,$first_menu_num);
        }else{
            $assign['first_menus'] = $first_menus;
            $assign['more_first_menus'] = [];
        }
        $this->assign($assign);
    }

    /**
     * 渲染js全局变量
     */
    public function init_assing_val(){
        $module = $this->request->module();
        $controller = strtolower($this->request->controller());
        $action = strtolower($this->request->action());

        $assign['js_global_var']['module'] = $module;
        $assign['js_global_var']['controller'] = $controller;
        $assign['js_global_var']['js_controller'] = str_replace('.','/',$controller);
        $assign['js_global_var']['action'] = $action;
        $assign['js_global_var']['table_id'] = $module . $controller . $action;

        $assign['has_del'] = $this->has_del;
        $assign['has_soft_del'] = $this->has_soft_del;
        $assign['role_ids'] = $this->role_ids ? $this->role_ids : [];
        $assign['rule_list'] = $this->rule_list ? $this->rule_list : [];
        $assign['is_show_search_btn'] = $this->is_show_search_btn;

        $assign['un_show_menus'] = $this->request->param('un_show_menus');

        $assign['ref'] = intval( $this->request->param('ref') );
        $this->ref = $assign['ref'];
        $assign['target_url'] = $this->module . '/' . $this->controller . '/' . $this->action;

        $this->assign($assign);
    }

    //批量操作列表，权限检测
    public function is_show_batch()
    {
        if (!$this->admin_user->is_super_manager) {
            $show_batch = false;
            foreach ($this->batch_action_list as $action) {
                if ($action == 'del') {
                    if ($this->has_del && in_array('del', $this->batch_action_list)) {
                        if (in_array($this->module . '/' . $this->controller . '/del', $this->rule_list)) {
                            $show_batch = true;
                            break;
                        }
                    }
                } else {
                    if (in_array($action, $this->batch_action_list)) {
                        if (in_array($this->module . '/' . $this->controller . '/' . $action, $this->rule_list)) {
                            $show_batch = true;
                            break;
                        }
                    }
                }
            }
        }else{
            if(count($this->batch_action_list)){
                $show_batch = true;
            }else{
                $show_batch = false;
            }
        }
        $this->assign('show_batch', $show_batch);
    }

    /**
     * 生成查询条件
     * @return array
     */
    public function build_params(){
        $where = [];
        //传递了search_param字段，就说明是进行筛选搜索
        $search_param = $this->request->param('search_param');
        if( $search_param ){
            foreach($search_param as $field=>$value_condition){
                if($value_condition['value'] != ''){
                    $condition = strtoupper($value_condition['condition']);
                    switch( $condition ){
                        case '=':
                            $where[] = "`{$field}` = '{$value_condition['value']}'";
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
                        case 'BETWEEN_STRTOTIME':
                            $arr_between = explode(' - ', $value_condition['value']);
                            $begin_time = strtotime($arr_between[0]);
                            $end_time = strtotime($arr_between[1]);
                            $where[] = "($field BETWEEN {$begin_time} and {$end_time})";
                            break;
                        case '>':
                            $where[] = "`{$field}` > {$value_condition['value']}";
                            break;
                        case '>=':
                            $where[] = "`{$field}` >= {$value_condition['value']}";
                            break;
                        case '<':
                            $where[] = "`{$field}` < {$value_condition['value']}";
                            break;
                        case '<=':
                            $where[] = "`{$field}` <= {$value_condition['value']}";
                            break;
                    }
                }
            }
        }
        $whereStr = implode(' AND ', $where);
        return $whereStr;
    }

    /**
     * 生成排序条件
     */
    public function build_order(){
        $order = [['id'=>'desc']];
        //传递了search_param字段，就说明是进行筛选搜索
        $order_param = $this->request->param('order_param');
        if($order_param && $order_param['field'] && $order_param['type']){
            $order = array_merge([$order_param['field']=>$order_param['type']],$order);
        }
        return $order;
    }

    /**
     * selectPage插件ajax请求时组合的查询条件,修改selectPage.js后的函数
     * @return string
     */
    public function select_page_build_params(){
        $field = $this->request->param('searchKey');
        $name = explode(',', $this->request->param('searchValue'));
        $where = [];
        foreach($name as $k=>$v){
            if($field != 'id'){
                $where[] = "{$field} LIKE '%{$v}%'";
            }else{
                $where[] = "id={$v}";
            }
        }
        $whereStr = implode(' OR ', $where);
        return $whereStr;
    }

    /**
     * selectPage插件ajax请求时组合的查询条件
     * @return string
     */
    public function build_select_page_params(){
        $where = [];
        $q_word = $this->request->param('q_word');
        $andOr = $this->request->param('andOr');
        $searchField = $this->request->param('searchField');
        $searchFieldStr = $searchField[0];
        if(is_array($q_word) && count($q_word)){
            foreach($q_word as $keyword){
                $where[] = "{$searchFieldStr} LIKE '%{$keyword}%'";
            }
        }
        $searchKey = $this->request->param('searchKey');
        $searchValue = $this->request->param('searchValue');
        if($searchKey && $searchValue){
            $where[] = "{$searchKey} in ({$searchValue})";
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