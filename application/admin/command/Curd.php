<?php
/**
 * Created by JunAdmin.
 * User: JunStar
 * Date: 18-9-20
 * Time: 下午8:56
 */
namespace app\admin\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;
use think\Db;
use think\Exception;

class Curd extends Command
{
    protected
        $curdModel,
        $id,
        $info,
        $curd_config,
        $controllerParam,
        $c_file_basename
    ;
    protected function configure(){
        $this->setName('curd')
            ->addOption('id', 'i', Option::VALUE_REQUIRED, 'autocreate_curd pk value', null)
            ->setDescription('Build CURD from table');
    }

    protected function execute(Input $input, Output $output){
        $this->curdModel = new \app\admin\model\autocreate\Curd();
        $this->id = $input->getOption('id') ?: 0;
        $this->info = $this->get_info_by_id();
        $this->curd_config = $this->format_info();

        $this->set_param();
        $this->create();
        $this->update_exec_time();

        $output->info('生成成功');
    }

    /**
     * @return mixed
     * @throws Exception
     */
    protected function get_info_by_id(){
        if(!$this->id){
            throw new Exception('id is error');
        }

        $info = $this->curdModel->get($this->id);
        if(!$info){
            throw new Exception('id is error');
        }

        return $info;
    }

    protected function format_info(){
        $field_list = json_decode($this->info['field_list'], true);
        $global = json_decode($this->info['global'], true);
        $curd_config['table_name'] = $this->info['table_name'];
        $curd_config['table_comment'] = $this->info['table_comment'];
        $curd_config['field_list'] = $field_list;
        $curd_config['global'] = $global;
        return $curd_config;
    }

    public function set_param(){
        $this->set_c_file_name();
        $this->set_controller_param();
    }

    public function create(){
        $this->c_controller();
    }

    public function update_exec_time(){
        //更新时间和次数
        if(!$this->info['exec_create_time']){
            $save['exec_create_time'] = time();
        }
        $save['exec_update_time'] = time();
        $save['exec_count'] = Db::raw('exec_count+1');
        $this->curdModel->where(['id'=>$this->id])->update($save);
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

    protected function get_name_by_table($table){
        $arr_table = explode('_', $table);
        array_shift($arr_table);//del_db_prefix
    }

    protected function set_c_file_name(){
        $arr_table = explode('_', $this->curd_config['table_name']);
        $basename = ucfirst($arr_table[count($arr_table)-1]);
        array_shift($arr_table);
        array_pop($arr_table);
        $str_table = implode(DS, $arr_table);
        $this->controller_c_file_name = app()->getAppPath() . 'admin/controller' . $str_table . DS . $basename . '.php';
    }

    protected function set_namespace(){

    }

    /**
     * 设置生成controller需要的参数
     * [
     *  'tpl_true_name'=>模板名,
     *  'data' => '执行替换模板的key=>value数组',
     *  'c_file_name' => '要生成的文件名'
     * ]
     */
    protected function set_controller_param(){
        $tpl_name = $this->get_tpl_true_name('controller' . DS . 'base');
        $data['controllerNamespace'] = '';
        $data['tableComment'] = $this->curd_config['table_comment'];
        $data['modelNamespace'] = '';
        $data['modelName'] = '';
        $c_file_name = $this->controller_c_file_name;
        $this->controllerParam = ['tpl_name'=>$tpl_name,'data'=>$data,'c_file_name'=>$c_file_name];
        dump($this->controllerParam);
    }

    //生成控制器的验证器
    protected function c_validate(){

    }

    //生成controller层
    protected function c_controller(){
        //根据表名获取文件名
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