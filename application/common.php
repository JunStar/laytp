<?php

use think\facade\Url;

if (!function_exists('addon_url')) {
    /**
     * Url生成
     * @param string        $url 路由地址
     * @param string|array  $vars 变量
     * @param bool|string   $suffix 生成的URL后缀
     * @param bool|string   $domain 域名
     * @return string
     */
    function addon_url($addon, $url = '', $vars = '', $suffix = true, $domain = false)
    {
        $addon_url = "/addons/$addon/$url";
        return Url::build($addon_url, $vars, $suffix, $domain);
    }
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

//删除目录
function deldir($dir){
    //先删除目录下的文件：
    $dh=opendir($dir);
    while ($file=readdir($dh)) {
        if($file!="." && $file!="..") {
            $fullpath=$dir."/".$file;
            if(!is_dir($fullpath)) {
                unlink($fullpath);
            } else {
                deldir($fullpath);
            }
        }
    }

    closedir($dh);
    //删除当前文件夹：
    if(rmdir($dir)) {
        return true;
    } else {
        return false;
    }
}

/**
 * curl方式请求远程地址
 * @param $url url地址
 * @param bool $data post的数据
 * @return bool|string
 */
function request_by_curl( $url, $data=FALSE )
{
    $ch = curl_init();
    curl_setopt ( $ch, CURLOPT_URL, $url );
    curl_setopt ( $ch, CURLOPT_HEADER, 0 );
    curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT, 1);
    if( is_array( $data ) && count( $data ) > 0 )
    {
        curl_setopt ( $ch, CURLOPT_POST, 1 );
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data );
    }
    $contents = curl_exec ( $ch );
    curl_close ( $ch );
    return $contents;
}
