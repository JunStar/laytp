<?php
use think\facade\Url;

/**
 * 生成插件url地址
 * @param $addon 插件名
 * @param string $url
 * @param string $vars
 * @param bool $suffix
 * @param bool $domain
 * @return string
 */
function addon_url($addon, $url = '', $vars = '', $suffix = true, $domain = false)
{
    $addon_url = "/addons/$addon/$url";
    return Url::build($addon_url, $vars, $suffix, $domain);
}

function getSelectMenuIds($menu_tree_obj, $id, $init=false){
    static $select_menu_ids;
    if($init){
        $select_menu_ids = [];
    }
    $tree = $menu_tree_obj->getTreeArray($id);
    if($tree){
        $select_menu_ids[] = $tree[0]['id'];
        if(count($tree[0]['children'])){
            getSelectMenuIds($menu_tree_obj,$tree[0]['id']);
        }
        return $select_menu_ids;
    }else{
        return [];
    }
}

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
 * 统一处理post数据
 * @param $post
 * @return mixed
 */
function filterPostData($post){
    if(!$post){
        return [];
    }
    //处理数组
    foreach($post as $k=>$v){
        if(is_array($v)){
            $post[$k] = implode(',',$v);
        }
    }
    return $post;
}

//获取插件js目录
function getAddonJSDir($addon,$module){
    return "/addons/$addon/$module/javascript";
}

//获取插件css目录
function getAddonCSSDir($addon,$module){
    return "/addons/$addon/static/$module/css";
}

//获取插件image目录
function getAddonImageDir($addon,$module){
    return "/addons/$addon/static/$module/image";
}

/**
 * 二维数组，外层索引替换成item内某个索引对应的值
 * @param $array
 * @param $field
 * @return array
 * @example
 *  $array = [
 *      0   =>  ['field'=>'id','comment'=>'ID'],
 *      1   =>  ['field'=>'name','comment'=>'标题']
 *  ];
 *  $result = arrToMap($array, 'field');
 *  $result结果：
 *  $result = [
 *      'id'    =>  ['field'=>'id','comment'=>'ID'],
 *      'name'  =>  ['field'=>'name','comment'=>'标题']
 *  ];
 */
function arr_to_map($array, $field){
    $result = [];
    foreach($array as $k=>$v){
        if(isset($v[$field])){
            $result[$v[$field]] = $v;
        }
    }
    return $result;
}

/**
 * 获取二维数组中某个key组成的数组
 * @param $array
 * @param $field
 * @return array
 */
function get_arr_by_key($array, $field){
    $result = [];
    foreach($array as $k=>$v){
        if(isset($v[$field])){
            $result[] = $v[$field];
        }
    }
    return $result;
}

/**
 * 执行命令行，使用此函数可以调用命令行程序，不需要application/command.php文件注册命令行
 * @param string $command_class_name 命令行完整类名
 * @param array $argv 命令行参数
 * @return string 返回的信息
 * @example
 * exec_command('app\admin\command\Curd',['--id=1','--name=test']);
 * 类似于执行:
 * php think curd --id=1 --name=test
 */
function exec_command($command_class_name, $argv=[]){
    $input = new \think\console\Input($argv);
    $command = app($command_class_name);
    $output = app('library\Output');
    try {
        $command->run($input, $output);
        $result['msg'] = implode("\n", $output->getMessage());
        $result['code'] = 1;
    } catch (Exception $e) {
        $result['msg'] = $e->getFile() . '<br />' . $e->getLine() . '<br />' . $e->getMessage() . '<br />' . $e->getCode();
        $result['code'] = 0;
    }
    return $result;
}

/**
 * 检测数组中的值是否都为true，启用数据库事务时能用到
 *      注意，更新操作返回影响数据库行数，当没有对数据进行更改时，正常操作数据库会返回0，表示影响了0行，
 *      由于0在PHP中表示false，但是在实际业务中，有可能影响0行也允许提交事务，
 *      此时需要根据业务需求，在controller层调用checkRes函数时，对入参$result进行判断，返回为0时设置为false还是true，
 *      以便用于判断最终执行回滚还是提交事务的操作
 * @param $result
 * @return bool
 */
function check_res($result){
    foreach($result as $v){
        if(!$v){
            return false;
        }
    }
    return true;
}
