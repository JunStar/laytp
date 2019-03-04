<?php
/**
 * Created by JunAdmin.
 * User: JunStar
 * Date: 18-9-20
 * Time: 下午8:56
 * Example: php think curd -i 1
 */
namespace app\admin\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;
use think\Db;
use think\Exception;
use think\facade\Config;

class Curd extends Command
{
    protected
        $curdModel,//autocreate_curdModel
        $id,//autocreate_curd主键id
        $info,//autocreate_curd主键id对应的数据
        $curd_config,//分析info后，主要是json_decode字段内容后，生成的配置信息
        $model_app_name,//模型所在app名称，全局模型就在common下，否则就在admin下
        $controller_model_class_name,//控制器和模型的类名
        $mid_name,//中间名称，比如表名为ja_test_a_b那么这里的mid_name就是/test/a/B,拼接控制器和模型文件的路径和namespace都需要用到
        $controller_array_const,//控制器层数组常量
        $controller_c_file_name,//需要生成的控制器文件的文件名
        $model_array_const,//模型层数组常量
        $model_c_file_name,//需要生成的模型层文件的文件名
        $js_c_file_name,//需要生成的js文件的文件名
        $html_array_const_js_param,//数组常量渲染成js常量
        $html_index_c_file_name,//需要生成的html首页文件的文件名
        $html_add_c_file_name,//需要生成的html添加页面文件的文件名
        $html_edit_c_file_name,//需要生成的html编辑页面文件的文件名
        $controllerParam,//生成控制器文件的参数,是一个数组['tpl_name'=>使用到的模板名称,'data'=>模板中需要替换的数据,'c_file_name'=>需要生成的文件名称]
        $modelParam,//生成模型层文件的参数,是一个数组['tpl_name'=>使用到的模板名称,'data'=>模板中需要替换的数据,'c_file_name'=>需要生成的文件名称]
        $jsParam,//生成js文件的参数,是一个数组['tpl_name'=>使用到的模板名称,'data'=>模板中需要替换的数据,'c_file_name'=>需要生成的文件名称]
        $htmlIndexParam,//生成html首页文件的参数,是一个数组['tpl_name'=>使用到的模板名称,'data'=>模板中需要替换的数据,'c_file_name'=>需要生成的文件名称]
        $htmlAddParam,//生成html添加页面文件的参数,是一个数组['tpl_name'=>使用到的模板名称,'data'=>模板中需要替换的数据,'c_file_name'=>需要生成的文件名称]
        $htmlEditParam//生成html编辑页面文件的参数,是一个数组['tpl_name'=>使用到的模板名称,'data'=>模板中需要替换的数据,'c_file_name'=>需要生成的文件名称]
    ;
    protected function configure(){
        $this->setName('curd')
            ->addOption('id', 'i', Option::VALUE_REQUIRED, 'autocreate_curd pk value', null)
            ->setDescription('Build CURD from table');
    }

    protected function execute(Input $input, Output $output){
        $this->curdModel = model('admin/autocreate.Curd');
        $this->id = $input->getOption('id') ?: 0;

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
        $this->info = $this->get_info_by_id();
        $this->curd_config = $this->format_info();
        $this->model_app_name = $this->curd_config['global']['common_model'] ? 'common' : 'admin';

        $this->set_mid_file_name();
        $this->set_c_file_name();
        $this->set_controller_param();
        $this->set_model_param();
        $this->set_js_param();
        $this->set_html_param();
    }

    public function create(){
        $this->c_html();
        $this->c_js();
        $this->c_controller();
        $this->c_model();
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
    protected function get_replaced_tpl($name, $data=[])
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

    protected function set_mid_file_name(){
        $arr_table = explode('_', $this->curd_config['table_name']);
        $basename = ucfirst($arr_table[count($arr_table)-1]);
        $this->controller_model_class_name = $basename;
        array_shift($arr_table);
        array_pop($arr_table);
        if(count($arr_table)){
            $str_table = implode(DS, $arr_table) . '/' . $basename;
        }else{
            $str_table = $basename;
        }
        $this->mid_name = $str_table;
    }

    //设置所有需要生成的文件名
    protected function set_c_file_name(){
        $this->controller_c_file_name = app()->getAppPath() . 'admin' . DS . 'controller' . DS . $this->mid_name . '.php';
        $this->model_c_file_name = app()->getAppPath() . $this->model_app_name . DS . 'model' . DS . $this->mid_name . '.php';
        $this->js_c_file_name = app()->getRootPath() . 'public' . DS . 'static' . DS . 'admin' . DS . 'javascript' . DS . strtolower($this->mid_name) . '.js';
        $this->html_index_c_file_name = app()->getAppPath() . 'admin' . DS . 'view' . DS . strtolower($this->mid_name) . DS . 'index.html';
        $this->html_add_c_file_name = app()->getAppPath() . 'admin' . DS . 'view' . DS . strtolower($this->mid_name) . DS . 'add.html';
        $this->html_edit_c_file_name = app()->getAppPath() . 'admin' . DS . 'view' . DS . strtolower($this->mid_name) . DS . 'edit.html';
    }

    /**
     * 设置生成controller需要的参数
     * [
     *  'tpl_name'=>模板名,
     *  'data' => '执行替换模板的key=>value数组',
     *  'c_file_name' => '要生成的文件名'
     * ]
     */
    protected function set_controller_param(){
        $tpl_name = 'controller' . DS . 'base';
        $data['controllerNamespace'] = str_replace('/','\\',dirname( 'app/admin/controller/'.$this->mid_name ));
        $data['tableComment'] = $this->curd_config['table_comment'];
        $data['modelName'] = strtolower( str_replace('/','_',$this->mid_name) );
        $data['modelClassName'] = $this->controller_model_class_name;
        $data['modelNamespace'] = str_replace('/','\\',dirname( 'app/'.$this->model_app_name.'/model/'.$this->mid_name ));
        $data['controllerClassName'] = $this->controller_model_class_name;
        $this->controllerParam = ['tpl_name'=>$tpl_name,'data'=>$data,'c_file_name'=>$this->controller_c_file_name];
    }

    /**
     * 设置生成model需要的参数
     * [
     *  'tpl_name'=>模板名,
     *  'data' => '执行替换模板的key=>value数组',
     *  'c_file_name' => '要生成的文件名'
     * ]
     */
    protected function set_model_param(){
        $tpl_name = 'model' . DS . 'base';
        $arr_table_name = explode('_', $this->curd_config['table_name']);
        $table_prefix = $arr_table_name['0'] . '_';
        $data['tableName'] = '';
        if($table_prefix != Config::get('database.prefix')){
            $data['tableName'] = 'protected $table = \''.$this->curd_config['table_name'].'\';';
        }
        $data['tableComment'] = $this->curd_config['table_comment'];
        $data['modelName'] = strtolower( str_replace('/','_',$this->mid_name) );
        $data['modelClassName'] = $this->controller_model_class_name;
        $data['modelNamespace'] = str_replace('/','\\',dirname( 'app/'.$this->model_app_name.'/model/'.$this->mid_name ));
        $this->modelParam = ['tpl_name'=>$tpl_name,'data'=>$data,'c_file_name'=>$this->model_c_file_name];
    }

    /**
     * 设置生成js需要的参数
     * [
     *  'tpl_name'=>模板名,
     *  'data' => '执行替换模板的key=>value数组',
     *  'c_file_name' => '要生成的文件名'
     * ]
     */
    protected function set_js_param(){
        $tpl_name = 'js';
        $cols = "";
        $has_first_cols = false;//是否已经有了正常的第一行数据
        //是否隐藏主键列
        if(!$this->curd_config['global']['hide_pk']){
            $has_first_cols = true;
            $pk = model('admin/InformationSchema')->getPkInfo($this->curd_config['table_name']);
            $temp = "{field:'{$pk['pk']}',title:'{$pk['field_comment']}',fixed:'left',align:'center',width:80}\n";
            $cols .= $temp;
        }else{
            $pk = model('admin/InformationSchema')->getPkInfo($this->curd_config['table_name']);
            $temp = "//{field:'{$pk['pk']}',title:'{$pk['field_comment']}',fixed:'left',align:'center',width:80}\n";
            $cols .= $temp;
        }
        //是否生成序号列
        if($this->curd_config['global']['create_number']){
            if($has_first_cols){
                $temp = "\t\t\t\t,{field:'layui_number',title:'序号',fixed:'left',align:'center',width:80,type:'numbers'}\n";
                $cols .= $temp;
            }else{
                $temp = "\t\t\t\t{field:'layui_number',title:'序号',fixed:'left',align:'center',width:80,type:'numbers'}\n";
                $cols .= $temp;
            }
            if(!$has_first_cols) $has_first_cols = true;
        }
        $show_fields = explode(',', $this->curd_config['global']['show_fields']);
        $all_fields = $this->curd_config['global']['all_fields'];
        $fields_list = arr_to_map($this->curd_config['field_list'], 'field_name');
        foreach($all_fields as $k=>$v){
            if( !in_array($v['field_name'], $show_fields) ){
                $temp = "\t\t\t\t//,{field:'{$v['field_name']}',title:'{$v['field_comment']}',align:'center'}\n";
            }else{
                if(!$has_first_cols){
                    $has_first_cols = true;
                    $temp = "\t\t\t\t{field:'{$v['field_name']}',title:'{$v['field_comment']}'";
                }else{
                    $temp = "\t\t\t\t,{field:'{$v['field_name']}',title:'{$v['field_comment']}'";
                }
                if($fields_list[$v['field_name']]['table_width'] !== '自适应' && $fields_list[$v['field_name']]['table_width']){
                    $temp .= ",width:".$fields_list[$v['field_name']]['table_width'];
                }
                if($fields_list[$v['field_name']]['table_min_width'] !== '使用全局配置' && $fields_list[$v['field_name']]['table_min_width']){
                    $temp .= ",minWidth:".$fields_list[$v['field_name']]['table_min_width'];
                }
//                if($fields_list[$v['field_name']]['table_templet']){
//                    if($fields_list[$v['field_name']]['table_templet'] == 'checkbox'){
//                        $temp .= ",templet:''";
//                    }else if($fields_list[$v['field_name']]['table_templet'] == 'radio'){
//                        $temp .= ",templet:'<div><input type=\"checkbox\" name=\"sex\" value=\"{{d.id}}\" lay-skin=\"switch\" lay-text=\"女|男\" lay-filter=\"sexDemo\"></div>'";
//                    }
//                }
                $temp .= ",align:'".$fields_list[$v['field_name']]['table_align']."'";
                if($fields_list[$v['field_name']]['table_additional_unresize']){
                    $temp .= ",unresize:true";
                }
                if($fields_list[$v['field_name']]['table_additional_sort']){
                    $temp .= ",sort:true";
                }
                if($fields_list[$v['field_name']]['table_additional_edit']){
                    $temp .= ",edit:'text'";
                }
                $temp .= "}\n";
            }
            $cols .= $temp;
        }
        if(isset($this->curd_config['global']['hide_del']) && $this->curd_config['global']['hide_del']){
            $temp = "\t\t\t\t,{field:'operation',title:'操作',align:'center',toolbar:'#operation_only_edit',fixed:'right',width:200}";
        }else{
            $temp = "\t\t\t\t,{field:'operation',title:'操作',align:'center',toolbar:'#operation',fixed:'right',width:200}";
        }
        $cols .= $temp;
        $data['cols'] = $cols;
        $data['close_page'] = $this->curd_config['global']['close_page'] ? '//' : '';
        $data['cellMinWidth'] = $this->curd_config['global']['cell_min_width'] ?: 80;
        $this->jsParam = ['tpl_name'=>$tpl_name,'data'=>$data,'c_file_name'=>$this->js_c_file_name];
    }

    /**
     * 设置生成表格html需要的参数
     * [
     *  'tpl_name'=>模板名,
     *  'data' => '执行替换模板的key=>value数组',
     *  'c_file_name' => '要生成的文件名'
     * ]
     */
    protected function set_html_param(){
        $index_tpl_name = 'html' . DS . 'index';
        $index_data['jsFileName'] = strtolower(strtolower($this->mid_name));

        $index_search_form = [];
        $search_fields = explode(',', $this->curd_config['global']['search_fields']);
        foreach($search_fields as $k=>$v){
            foreach($this->curd_config['field_list'] as $fk=>$fv){
                if($v == $fv['field_name']){
                    $search_item_content = $this->get_search_form_item($fv,'add');
                    $index_search_form[] = $this->get_search_form_group($fv['field_comment'], $search_item_content);
                    break;
                }
            }
        }

        $index_data['searchForm'] = implode("\n\n", $index_search_form);
        $this->htmlIndexParam = ['tpl_name'=>$index_tpl_name,'data'=>$index_data,'c_file_name'=>$this->html_index_c_file_name];

        $add_data = [];
        $edit_data = [];
        foreach($this->curd_config['field_list'] as $k=>$v){
            $add_item_content = $this->get_form_item($v,'add');
            $edit_item_content = $this->get_form_item($v,'edit');
            $add_data[] = $this->get_form_group($v['field_comment'], $add_item_content);
            $edit_data[] = $this->get_form_group($v['field_comment'], $edit_item_content);
        }

        $add_tpl_name = 'html' . DS . 'add';
        $add_form['formContent'] = implode("\n\n", $add_data);
        $this->htmlAddParam = ['tpl_name'=>$add_tpl_name,'data'=>$add_form,'c_file_name'=>$this->html_add_c_file_name];

        $edit_tpl_name = 'html' . DS . 'edit';
        $edit_data['formContent'] = implode("\n\n", $edit_data);
        $this->htmlEditParam = ['tpl_name'=>$edit_tpl_name,'data'=>$edit_data,'c_file_name'=>$this->html_edit_c_file_name];
    }

    protected function get_form_item($info,$type='add'){
        $func = 'get_'.$info['form_type'].'_html';
        return $this->$func($info,$type);
    }

    /**
     * 获取表单分组数据
     * @param string $field
     * @param string $content
     * @return string
     */
    protected function get_form_group($field, $content)
    {
        return <<<EOD
    <div class="layui-form-item">
        <label class="layui-form-label">{$field}</label>
        <div class="layui-input-block">
            {$content}
        </div>
    </div>
EOD;
    }

    protected function get_search_form_item($info){
        $func = 'get_search_'.$info['form_type'].'_html';
        return $this->$func($info);
    }

    protected function get_search_form_group($field, $content)
    {
        return <<<EOD
    <div class="layui-form-item layui-inline">
        <label class="layui-form-label">{$field}</label>
        <div class="layui-input-inline">
            {$content}
        </div>
    </div>
EOD;
    }

    //生成控制器的验证器
    protected function c_validate(){

    }

    protected function set_controller_array_const($field_name){
        if(!isset($this->controller_array_const[$field_name])){
            $this->controller_array_const[$field_name] = '$assign[\''.$field_name.'\'] = $this->model->getArrayConstList(\''.$field_name.'\');';
        }
    }

    //生成controller层
    protected function c_controller(){
        if(!empty($this->controller_array_const)){
            $this->controllerParam['data']['arrayConstAssign'] = implode("\n", $this->controller_array_const);
            $this->controllerParam['data']['arrayConstAssign'] .= "\n\t\t".'$this->assign($assign);';
        }else{
            $this->controllerParam['data']['arrayConstAssign'] = '';
        }
        $this->write_to_file($this->controllerParam['tpl_name'], $this->controllerParam['data'], $this->controllerParam['c_file_name']);
    }

    //设置模型层的数组常量
    protected function set_model_array_const($field_name, $array){
        //设置常量
        $new_model_array_const = [];
        $new_model_array_const[] = "\t\t'{$field_name}' => [";
        $i=0;
        foreach($array as $k=>$v){
            if($i==0){
                $new_model_array_const[] = "\n\t\t\t".'\'' . $k . '\'=>\'' . $v . '\'';
            }else{
                $new_model_array_const[] = "\n\t\t\t".',\'' . $k . '\'=>\'' . $v . '\'';
            }
            $i++;
        }
        $new_model_array_const[] = "\n\t\t".'],';
        $new_model_array_const_str = implode("",$new_model_array_const);

        if(!isset($this->model_array_const[$field_name])){
            $this->model_array_const[$field_name] = $new_model_array_const_str;
        }
    }

    //生成model层文件
    protected function c_model(){
        if(!empty($this->model_array_const)){
            $this->modelParam['data']['arrayConst'] = implode("\n\n", $this->model_array_const);
        }else{
            $this->modelParam['data']['arrayConst'] = '';
        }
        //设置获取常量的函数
        if(!empty($this->model_array_const)){
            $array_const_function_ja = 'model' . DS . 'array_const_function';
            $this->modelParam['data']['getArrayConstListFunction'] = $this->get_replaced_tpl($array_const_function_ja);
        }else{
            $this->modelParam['data']['getArrayConstListFunction'] = '';
        }
        $this->write_to_file($this->modelParam['tpl_name'], $this->modelParam['data'], $this->modelParam['c_file_name']);
    }

    //生成js文件
    protected function c_js(){
        $this->write_to_file($this->jsParam['tpl_name'], $this->jsParam['data'], $this->jsParam['c_file_name']);
    }

    protected function set_html_array_const_js_param($field_name, $str){
        $this->html_array_const_js_param[$field_name] = $str;
    }

    //生成html模板文件
    protected function c_html(){
        if(!empty($this->html_array_const_js_param)){
            $this->htmlIndexParam['data']['jsParam'] = '<script>'.implode("\n", $this->html_array_const_js_param).'</script>';
        }
        $this->write_to_file($this->htmlIndexParam['tpl_name'], $this->htmlIndexParam['data'], $this->htmlIndexParam['c_file_name']);
        $this->write_to_file($this->htmlAddParam['tpl_name'], $this->htmlAddParam['data'], $this->htmlAddParam['c_file_name']);
        $this->write_to_file($this->htmlEditParam['tpl_name'], $this->htmlEditParam['data'], $this->htmlEditParam['c_file_name']);
    }

    /**
     * 接下来是html的各种控件模板内容生成，比如input、select、upload等等
     */


    /**
     * 获取input需要生成的html，在生成add和edit表单的时候可以用到
     * @param $info
     * @param $type 类型，add或者edit
     * @return string
     */
    protected function get_input_html($info,$type){
        $name = 'html' . DS . $type . DS . 'input';
        $data['field_name'] = $info['field_name'];
        $data['field_comment'] = $info['field_comment'];
        $data['verify'] = $info['form_additional'];
        if(!$info['form_empty']){
            $data['verify'] = $data['verify'] ? 'required|'.$data['verify'] : 'required';
        }
        return $this->get_replaced_tpl($name, $data);
    }

    /**
     * 获取input需要生成的html，在生成搜索表单时用到
     * @param $info
     * @return string
     */
    protected function get_search_input_html($info){
        $name = 'html' . DS . 'search' . DS . 'input';
        $data['field_name'] = $info['field_name'];
        $data['field_comment'] = $info['field_comment'];
        return $this->get_replaced_tpl($name, $data);
    }

    /**
     * 获取input需要生成的html，在生成add和edit表单的时候可以用到
     * @param $info
     * @param $type 类型，add或者edit
     * @return string
     */
    protected function get_textarea_html($info,$type){
        $name = 'html' . DS . $type . DS . 'textarea';
        $data['field_name'] = $info['field_name'];
        $data['field_comment'] = $info['field_comment'];
        $data['verify'] = $info['form_empty'] ? '' : 'required';
        return $this->get_replaced_tpl($name, $data);
    }

    /**
     * 获取input需要生成的html，在生成搜索表单时用到
     * @param $info
     * @return string
     */
    protected function get_search_textarea_html($info){
        $name = 'html' . DS . 'search' . DS . 'textarea';
        $data['field_name'] = $info['field_name'];
        $data['field_comment'] = $info['field_comment'];
        return $this->get_replaced_tpl($name, $data);
    }

    /**
     * 获取input需要生成的html，在生成add和edit表单的时候可以用到
     * @param $info
     * @param $type 类型，add或者edit
     * @return string
     */
    protected function get_radio_html($info,$type){
        $items = explode(",", $info['form_additional']);
        $radio_items = [];//待选项数组
        $default_value = '';//默认值
        $model_array_const = [];
        foreach($items as $k=>$v){
            $temp = explode('=', $v);
            if($k==0){
                $default_value = $temp[0];
            }

            if($temp[0]=='default'){
                $default_value = $temp[1];
            }else{
                $radio_items[] = ['value'=>$temp[0], 'text'=>$temp[1]];
                $model_array_const[(string)$temp[0]] = $temp[1];
            }
        }
        /**
         * 待选项数组个数和默认值对表单元素展示的影响：
         *  1.待选项数组个数为2时
         *      表单元素为开关形式展示方式，默认值为非选中状态
         *  2.待选项个数超过2时
         *      表单元素为普通单选按钮展示方式，默认值为选中状态
         */
        if(count($radio_items) == 2){
            $name = 'html' . DS . $type . DS . 'radio_switch';
            $data['field_name'] = $info['field_name'];
            $data['default_value'] = $default_value;
            $data['checked_value'] = ($radio_items[0]['value'] == $default_value) ? $radio_items[1]['value'] : $radio_items[0]['value'];
            if(($radio_items[0]['value'] == $default_value)) {
                $data['lay_text'] = $radio_items[1]['text'] . '|' . $radio_items[0]['text'];
            }else{
                $data['lay_text'] = $radio_items[0]['text'] . '|' . $radio_items[1]['text'];
            }
            $this->set_model_array_const($info['field_name'], $model_array_const);
            return $this->get_replaced_tpl($name, $data);
        }else if(count($radio_items) > 2){
            $name = 'html' . DS . $type . DS . 'radio';
            $radio_html = '';
            foreach($radio_items as $k=>$v){
                $temp_data['field_name'] = $info['field_name'];
                $temp_data['value'] = $v['value'];
                $temp_data['title'] = $v['text'];
                $temp_data['checked_status'] = ($v['value'] == $default_value) ? 'checked="checked"' : '';
                $radio_html .= $this->get_replaced_tpl($name, $temp_data) . "\n\t\t\t";
            }
            $radio_html = rtrim($radio_html,"\n\t\t\t");
            $this->set_model_array_const($info['field_name'], $model_array_const);
            return $radio_html;
        }
    }

    protected function get_search_radio_html($info){
        $name = 'html' . DS . 'search' . DS . 'radio';
        $data['field_name'] = $info['field_name'];
        $items = explode(',', $info['form_additional']);
        $option_items = [];
        foreach($items as $k=>$v){
            $temp = explode('=', $v);
            $option_items[] = ['value'=>$temp[0], 'text'=>$temp[1]];
        }
        $options = '';
        foreach($option_items as $k=>$v){
            $options .= "\t\t\t\t".'<option value="'.$v['value'].'">'.$v['text'].'</option>'."\n";
        }
        $options = "\t\t\t\t".'<option value=""></option>' . "\n" . rtrim($options,"\n");
        $data['options'] = $options;
        $data['field_comment'] = $info['field_comment'];
        return $this->get_replaced_tpl($name, $data);
    }

    protected function get_checkbox_html($info,$type){
        $name = 'html' . DS . $type . DS . 'checkbox';
        $data['field_name'] = $info['field_name'];
        $items = explode(',', $info['form_additional']);
        $default_value = '';
        $option_items = [];
        $model_array_const = [];
        $checkbox_html = '';
        foreach($items as $k=>$v){
            $temp = explode('=', $v);
            if($temp[0]=='default'){
                $default_value = $temp[1];
            }else{
                $option_items[] = ['value'=>$temp[0], 'text'=>$temp[1]];
                $model_array_const[(string)$temp[0]] = $temp[1];
            }
        }

        $default_value_arr = [];
        if($default_value){
            $default_value_arr = explode(';', $default_value);
        }

        foreach($option_items as $k=>$v){
            if($type == 'add'){
                $data['checked'] = in_array($v['value'], $default_value_arr) ? 'checked="checked"' : '';
            }
            $data['value'] = $v['value'];
            $data['text'] = $v['text'];
            $checkbox_html .= $this->get_replaced_tpl($name, $data)."\n\t\t\t";
        }
        $checkbox_html = rtrim($checkbox_html,"\n\t\t\t");

        $this->set_model_array_const($info['field_name'], $model_array_const);
        return $checkbox_html;
    }

    protected function get_search_checkbox_html($info){
        $name = 'html' . DS . 'search' . DS . 'select_multi';
        $data['field_name'] = $info['field_name'];
        $items = explode(',', $info['form_additional']);
        $data['max'] = count($items);
        $option_items = [];
        $model_array_const = [];
        foreach($items as $k=>$v){
            $temp = explode('=', $v);
            $option_items[] = ['id'=>$temp[0], 'name'=>$temp[1]];
            $model_array_const[(string)$temp[0]] = $temp[1];
        }
        $this->set_controller_array_const($info['field_name'], $model_array_const);
        $this->set_html_array_const_js_param($info['field_name'], 'const '.$info['field_name'].' = {:getSelectMultiJsConst($'.$info['field_name'].')};');
        return $this->get_replaced_tpl($name, $data);
    }

    /**
     * 获取select需要生成的html，在生成add和edit表单的时候可以用到
     * @param $info
     * @param $type 类型，add或者edit
     * @return string
     */
    protected function get_select_html($info,$type){
        if($info['form_additional']['single_multi'] == 'single'){
            return $this->get_select_single_html($info,$type);
        }else if($info['form_additional']['single_multi'] == 'multi'){
            return $this->get_select_multi_html($info,$type);
        }
    }

    protected function get_search_select_html($info){
        if($info['form_additional']['single_multi'] == 'single'){
            return $this->get_search_select_single_html($info);
        }else if($info['form_additional']['single_multi'] == 'multi'){
            return $this->get_search_select_multi_html($info);
        }
    }

    protected function get_select_single_html($info,$type){
        $name = 'html' . DS . $type . DS . 'select_single';
        $data['field_name'] = $info['field_name'];
        $items = explode(',', $info['form_additional']['values']);
        $default_value = '';
        $option_items = [];
        $model_array_const = [];
        foreach($items as $k=>$v){
            $temp = explode('=', $v);
            if($temp[0]=='default'){
                $default_value = $temp[1];
            }else{
                $option_items[] = ['value'=>$temp[0], 'text'=>$temp[1]];
                $model_array_const[(string)$temp[0]] = $temp[1];
            }
        }
        $options = '';
        foreach($option_items as $k=>$v){
            if($type == 'add'){
                if($default_value == $v['value']){
                    $options .= "\t\t\t\t".'<option value="'.$v['value'].'" selected="selected">'.$v['text'].'</option>'."\n";
                }else{
                    $options .= "\t\t\t\t".'<option value="'.$v['value'].'">'.$v['text'].'</option>'."\n";
                }
            }else{
                $options .= "\t\t\t\t".'<option value="'.$v['value'].'" {if $'.$info['field_name'].' == \''.$v['value'].'\'}selected="selected"{/if}>'.$v['text'].'</option>'."\n";
            }
        }
        $options = "\t\t\t\t".'<option value=""></option>' . "\n" . rtrim($options,"\n");
        $data['options'] = $options;
        $data['field_comment'] = $info['field_comment'];
        $data['verify'] = $info['form_empty'] ? '' : 'required';
        $this->set_model_array_const($info['field_name'], $model_array_const);
        return $this->get_replaced_tpl($name, $data);
    }

    protected function get_search_select_single_html($info){
        $name = 'html' . DS . 'search' . DS . 'radio';
        $data['field_name'] = $info['field_name'];
        $items = explode(',', $info['form_additional']['values']);
        $option_items = [];
        foreach($items as $k=>$v){
            $temp = explode('=', $v);
            $option_items[] = ['value'=>$temp[0], 'text'=>$temp[1]];
        }
        $options = '';
        foreach($option_items as $k=>$v){
            $options .= "\t\t\t\t".'<option value="'.$v['value'].'">'.$v['text'].'</option>'."\n";
        }
        $options = "\t\t\t\t".'<option value=""></option>' . "\n" . rtrim($options,"\n");
        $data['options'] = $options;
        $data['field_comment'] = $info['field_comment'];
        return $this->get_replaced_tpl($name, $data);
    }

    protected function get_select_multi_html($info,$type){
        $name = 'html' . DS . $type . DS . 'select_multi';
        $data['field_name'] = $info['field_name'];
        $data['max'] = intval( $info['form_additional']['max'] );
        $items = explode(',', $info['form_additional']['values']);
        $default_value = '';
        $option_items = [];
        $model_array_const = [];
        foreach($items as $k=>$v){
            $temp = explode('=', $v);
            if($temp[0]=='default'){
                $default_value = $temp[1];
            }else{
                $option_items[] = ['id'=>$temp[0], 'name'=>$temp[1]];
                $model_array_const[(string)$temp[0]] = $temp[1];
            }
        }
        if($type == 'add'){
            $data['selected_data'] = $default_value ? '[\''.str_replace(';','\',\'',$default_value).'\']' : '';
        }
        $data['verify'] = $info['form_empty'] ? '' : 'required';
        $this->set_model_array_const($info['field_name'], $model_array_const);
        $this->set_controller_array_const($info['field_name'], $model_array_const);
        $this->set_html_array_const_js_param($info['field_name'], 'const '.$info['field_name'].' = {:getSelectMultiJsConst($'.$info['field_name'].')};');
        return $this->get_replaced_tpl($name, $data);
    }

    protected function get_search_select_multi_html($info){
        $name = 'html' . DS . 'search' . DS . 'select_multi';
        $data['field_name'] = $info['field_name'];
        $items = explode(',', $info['form_additional']['values']);
        $data['max'] = count($items);
        $option_items = [];
        $model_array_const = [];
        foreach($items as $k=>$v){
            $temp = explode('=', $v);
            $option_items[] = ['id'=>$temp[0], 'name'=>$temp[1]];
            $model_array_const[(string)$temp[0]] = $temp[1];
        }
        $this->set_controller_array_const($info['field_name'], $model_array_const);
        $this->set_html_array_const_js_param($info['field_name'], 'const '.$info['field_name'].' = {:getSelectMultiJsConst($'.$info['field_name'].')};');
        return $this->get_replaced_tpl($name, $data);
    }

    protected function get_select_page_html($info,$type){
        $name = 'html' . DS . $type . DS . 'select_page';
        $data['field_name'] = $info['field_name'];

        $search_url = substr($info['form_additional']['table_name'], strlen(Config::get('database.prefix')) );
        $search_url = str_replace('_','.', $search_url);
        $data['search_url'] = "{:url('admin/".$search_url."/index')}";

        $data['search_field'] = $info['form_additional']['search_field_name'];
        $data['show_field'] = $info['form_additional']['show_field_name'];

        if( intval($info['form_additional']['max']) ){
            $data['max'] = 'xm-select-max='.intval($info['form_additional']['max']);
        }else{
            $data['max'] = '';
        }

        if( $info['form_additional']['single_multi'] == 'single' ){
            $data['single_multi'] = 'xm-select-radio=""';
        }else{
            $data['single_multi'] = '';
        }

        return $this->get_replaced_tpl($name, $data);
    }

    protected function get_search_select_page_html($info){
        $name = 'html' . DS . 'search' . DS . 'select_page';
        return $this->get_replaced_tpl($name, []);
    }
}