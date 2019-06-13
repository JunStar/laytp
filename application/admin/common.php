<?php

/**
 * 分页数据格式化成layui_table能用的数据
 * @param $data
 * @return \think\response\Json
 */
function layui_table_page_data($data){
    if( array_key_exists('total', $data) ){
        $json['code'] = ($data['total'] > 0) ? 0 : 1;
    }else{
        $data['total'] = 0;
        $json['code'] = 1;
    }
    $json['msg'] = '暂无数据';
    $json['count'] = $data['total'];

    if( array_key_exists('data', $data) ){
        $json['data'] = $data['data'];
    }else{
        $json['data'] = [];
    }
    return json($json);
}

/**
 * 获取不分页的layui_table数据
 * @param $data
 * @return \think\response\Json
 */
function layui_table_data($data){
    $count_data = count($data);
    $json['code'] = ($count_data > 0) ? 0 : 1;
    $json['msg'] = '暂无数据';
    $json['data'] = $data;
    return json($json);
}

function select_page_data($data){
    $return['list'] = $data['data'];
    $return['totalRow'] = $data['total'];
    return $return;
}

/**
 * 获取某个字段的静态常量值列表
 * @param $field_name
 * @param $field_val
 * @param $const
 * @return string
 */
function get_const_val($field_name, $field_val, $const){
    $result = [];
    $field_val_arr = explode(',', $field_val);
    foreach($const[$field_name] as $k=>$v){
        foreach($field_val_arr as $fv){
            if($fv == $k){
                $result[] = $v;
            }
        }
    }
    return implode(',', $result);
}

/**
 * 生成普通多选下拉框的Js常量
 * @param $array
 * @return false|string
 */
function getSelectMultiJsConst($array){
    $result = [];
    foreach($array as $v=>$name){
        $result[] = ['id'=>$v,'name'=>$name];
    }
    return json_encode($result);
}

/**
 * 生成复选框的Js常量
 * @param $array
 * @return false|string
 */
function getCheckboxJsConst($array){
    return json_encode($array);
}

/**
 * 统一处理post数据
 * @param $post
 * @return mixed
 */
function filterPostData($post){
    if(!$post){
        return [];
    }
    foreach($post as $k=>$v){
        if(is_array($v)){
            $post[$k] = implode(',',$v);
        }
    }
    return $post;
}

/**
 * 获取默认头像
 * @param $avatar
 * @return string
 */
function getDefaultAvatar($avatar){
    if(!$avatar){
        return '/static/admin/image/default_avatar.png';
    }else{
        return $avatar;
    }
}

/**
 * 判断是否登录状态
 * @return bool
 */
function isLogin(){
    $admin_user_id = Session::get('admin_user_id');
    if($admin_user_id){
        return $admin_user_id;
    }else{
        return false;
    }
}

function importRule($controller, $pid)
{
    $controller = str_replace('\\', '/', $controller);
    if (stripos($controller, '/') !== false) {
        $controllerArr = explode('/', $controller);
        end($controllerArr);
        $key = key($controllerArr);
        $controllerArr[$key] = ucfirst($controllerArr[$key]);
    } else {
        $key = 0;
        $controllerArr = [ucfirst($controller)];
    }

    $classSuffix = \think\facade\Config::get('controller_suffix') ? ucfirst(\think\facade\Config::get('url_controller_layer')) : '';
    $pathArr = $controllerArr;
    array_unshift($pathArr, '', 'application', 'admin', 'controller');
    $classFile = \think\facade\Env::get('root_path') . implode(DS, $pathArr) . $classSuffix;
    $classContent = file_get_contents($classFile);
    $uniqueName = uniqid("LayTp") . $classSuffix;
    $classContent = str_replace("class " . basename($controllerArr[$key],'.php') . $classSuffix . " ", 'class ' . $uniqueName . ' ', $classContent);
    $classContent = preg_replace("/namespace\s(.*);/", 'namespace ' . "app\\admin\\controller\\autocreate" . ";", $classContent);

    //临时的类文件
    $tempClassFile = \think\facade\Env::get('app_path') . "" . DS . "admin" . DS . "controller" . DS . "autocreate" . DS . $uniqueName . ".php";
    file_put_contents($tempClassFile, $classContent);
    $className = "app\\admin\\controller\\autocreate\\" . $uniqueName;
    //反射机制调用类的注释和方法名
    $reflector = new ReflectionClass($className);

    if (isset($tempClassFile)) {
        //删除临时文件
        @unlink($tempClassFile);
    }

    //只匹配公共的方法
    $methods = $reflector->getMethods(ReflectionMethod::IS_PUBLIC);
    $classComment = $reflector->getDocComment();

    //判断是否有启用软删除
    $allproperties = $reflector->getDefaultProperties();
    $soft_del_rule_list = [];
    if($allproperties['has_soft_del']){
        $soft_del_rule_list = ['recycle','renew','true_del'];
    }

    $name = '';

    foreach ($controllerArr as $k => $v) {
        $key = $k + 1;
        //驼峰转下划线
        $controllerNameArr = array_slice($controllerArr, 0, $key);
        foreach ($controllerNameArr as &$val) {
            $val = basename(strtolower(trim(preg_replace("/[A-Z]/", "_\\0", $val), "_")),'.php');
        }
        $name = implode('/', $controllerNameArr);
    }

    $rule_list = array_merge( ['index','add','edit','set_status','del'], $soft_del_rule_list );

    $menu_model = new \app\admin\model\auth\Menu();

    //过滤掉其它字符
    $menu_name = preg_replace(array('/^\/\*\*(.*)[\n\r\t]/u', '/[\s]+\*\//u', '/\*\s@(.*)/u', '/[\s|\*]+/u'), '', $classComment);
    $ruleArr[] = array('id' => null, 'pid' => $pid, 'rule' => 'admin/' . $name . '/index', 'icon' => 'layui-icon layui-icon-fire', 'name' => $menu_name, 'is_menu' => 1, 'is_hide' => '0');

    $name_list = ['index'=>'查看','add'=>'添加','edit'=>'编辑','del'=>'删除','set_status'=>'设置状态','recycle'=>'回收站','renew'=>'还原','true_del'=>'删除'];

    foreach($rule_list as $k=>$n){
        $rule = 'admin/' . $name. '/' . $n;
        $id = $menu_model->where('rule', $rule)->value('id');
        $id = $id ? $id : null;

        $ruleArr[] = array('id' => $id, 'pid' => $pid, 'rule' => $rule, 'icon' => 'layui-icon layui-icon-fire', 'name' => $name_list[$n], 'is_menu' => 0, 'is_hide' => '1');
    }

    $menu_model->isUpdate(false)->saveAll($ruleArr);
    return true;
}