<?php
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
        $result = implode("\n", $output->getMessage());
    } catch (Exception $e) {
        $result = implode("\n", $output->getMessage()) . "\n";
        $result .= $e->getMessage();
    }
    $result = trim($result);
    return $result;
}