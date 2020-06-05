<?php
//导入规则
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
    $classContent = preg_replace("/namespace\s(.*);/", 'namespace ' . "addons\\autocreate\\admin\\controller" . ";", $classContent);

    //临时的类文件
    $tempClassFile = \think\facade\Env::get('root_path') . 'addons' . DS . 'autocreate' . DS . 'admin' . DS . 'controller' . DS . $uniqueName . ".php";
    file_put_contents($tempClassFile, $classContent);
    $className = "addons\\autocreate\\admin\\controller\\" . $uniqueName;
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
        $name = implode('.', $controllerNameArr);
    }

    $menu_model = new \app\admin\model\auth\Menu();

    //过滤掉其它字符
    $menu_name = preg_replace(array('/^\/\*\*(.*)[\n\r\t]/u', '/[\s]+\*\//u', '/\*\s@(.*)/u', '/[\s|\*]+/u'), '', $classComment);
    $new_menu = array( 'pid' => $pid, 'rule' => 'admin/' . $name . '/index', 'icon' => 'layui-icon layui-icon-fire', 'name' => $menu_name, 'is_menu' => 1, 'is_hide' => '0');

    $new_pid = $menu_model->insertGetId($new_menu);

    $look = array( 'pid' => $new_pid, 'rule' => 'admin/' . $name . '/index', 'icon' => 'layui-icon layui-icon-fire', 'name' => '查看', 'is_menu' => 0, 'is_hide' => '0');
    $menu_model->insert($look);

    $name_list = ['index'=>'查看','add'=>'添加','edit'=>'编辑','del'=>'删除','set_status'=>'设置状态','recycle'=>'回收站','renew'=>'还原','true_del'=>'彻底删除'];

    $ruleArr = [];

    $rule_list = array_merge( ['add','edit','set_status','del'], $soft_del_rule_list );
    foreach($rule_list as $k=>$n){
        $rule = 'admin/' . $name. '/' . $n;
        $id = $menu_model->where('rule', $rule)->value('id');
        $id = $id ? $id : null;

        $ruleArr[] = array('id' => $id, 'pid' => $new_pid, 'rule' => $rule, 'icon' => 'layui-icon layui-icon-fire', 'name' => $name_list[$n], 'is_menu' => 0, 'is_hide' => '1');
    }

    $menu_model->isUpdate(false)->saveAll($ruleArr);
    return true;
}