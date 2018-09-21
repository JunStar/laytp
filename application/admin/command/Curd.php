<?php
/**
 * Created by JunAdmin.
 * User: JunStar
 * Date: 18-9-20
 * Time: 下午8:56
 */
namespace app\admin\command;

use app\admin\model\AutocreateCurdModel;
use think\console\Command;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;
use think\Db;
use think\Exception;

class Curd extends Command
{
    protected function configure(){
        $this->setName('curd')
            ->addOption('id', 'i', Option::VALUE_REQUIRED, 'autocreate_curd pk value', null)
            ->setDescription('Build CURD from table');
    }

    protected function execute(Input $input, Output $output){
        $id = $input->getOption('id') ?: 0;
        if(!$id){
            throw new Exception('id is error');
        }

        $autocreateCurdModel = new AutocreateCurdModel();
        $info = $autocreateCurdModel->get($id);
        if(!$info){
            throw new Exception('id is error');
        }

        $field_list = json_decode($info['field_list'], true);
        $global = json_decode($info['global']);

        $curd_config['table_name'] = $info['table_name'];
        $curd_config['table_comment'] = $info['table_comment'];
        $curd_config['field_list'] = $field_list;
        $curd_config['global'] = $global;
        $this->c_controller($curd_config);


        //更新时间和次数
        if(!$info['exec_create_time']){
            $save['exec_create_time'] = time();
        }
        $save['exec_update_time'] = time();
        $save['exec_count'] = Db::raw('exec_count+1');
        $autocreateCurdModel->where(['id'=>$id])->update($save);

        $output->info('生成成功');
    }

    /**
     * 写入到文件
     * @param string $name 模板文件名
     * @param array $data key=>value形式的替换数组
     * @param string $pathname 生成的绝对文件名
     * @return mixed
     */
    protected function write_to_file($name, $data, $pathname)
    {
        foreach ($data as $index => &$datum) {
            $datum = is_array($datum) ? '' : $datum;
        }
        unset($datum);
        $content = $this->get_replaced_tpl($name, $data);

        if (!is_dir(dirname($pathname))) {
            mkdir(dirname($pathname), 0755, true);
        }
        return file_put_contents($pathname, $content);
    }

    /**
     * 获取替换后的数据
     * @param string $name 模板文件名
     * @param array $data key=>value形式的替换数组
     * @return string
     */
    protected function get_replaced_tpl($name, $data)
    {
        foreach ($data as $index => &$datum) {
            $datum = is_array($datum) ? '' : $datum;
        }
        unset($datum);
        $search = $replace = [];
        foreach ($data as $k => $v) {
            $search[] = "{%{$k}%}";
            $replace[] = $v;
        }
        $tpl_true_name = $this->get_tpl_true_name($name);
        if (isset($this->tplList[$tpl_true_name])) {
            $tpl = $this->tplList[$tpl_true_name];
        } else {
            $this->tplList[$tpl_true_name] = $tpl = file_get_contents($tpl_true_name);
        }
        $content = str_replace($search, $replace, $tpl);
        return $content;
    }

    /**
     * 获取模板文件真实路径
     * @param string $name 举例： $name = 'controller' . DS . 'edit';
     * @return string
     */
    protected function get_tpl_true_name($name){
        return __DIR__ . DS . 'Curd' . DS . $name . '.ja';
    }

    //生成控制器的验证器
    protected function c_validate(){

    }

    //生成controller层
    protected function c_controller(){

    }

    //生成model层文件
    protected function c_model(){

    }

    //生成js文件
    protected function c_js(){

    }

    //生成html模板文件
    protected function c_html(){

    }
}