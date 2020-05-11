<?php
/**
 * Created by LayTp.
 * User: JunStar
 * Date: 2019-12-27
 * Time: 18:29:42
 * Example: php think curd -i 1
 */
namespace addons\autocreate\admin\command;

use app\admin\model\InformationSchema;
use think\console\Command;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;
use think\Db;
use think\facade\Config;

class CurdCategory extends Command
{
    protected
        $curdModel//autocreate_curdModel
        ,$id//autocreate_curd主键id
        ,$info//autocreate_curd主键id对应的数据
        ,$curd_config//分析info后，主要是json_decode字段内容后，生成的配置信息
    ;

    //ThinkPHP命令行配置函数
    protected function configure(){
        $this->setName('curd_category')
            ->addOption('id', 'i', Option::VALUE_REQUIRED, 'autocreate curd pk value', null)
            ->setDescription('Build CURD_CATEGORY from table');
    }

    //ThinkPHP命令行执行函数
    protected function execute(Input $input, Output $output){
        $this->curdModel = model('admin/autocreate.Curd');
        $this->id = $input->getOption('id') ?: 0;

        $this->set_param();
        $this->create();
        $this->update_exec_time();

        $output->info('生成成功');
    }

    /**
     * 根据主键ID获取数据
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

    /**
     * 设置参数，待生成
     * @throws Exception
     */
    public function set_param(){
        $this->info = $this->get_info_by_id();
        $this->curd_config = $this->format_info();
        $information = new InformationSchema();
        $field_list_db = $information->getFieldsComment($this->curd_config['table_name']);
        $this->field_list_map = arr_to_map($field_list_db->toArray(),'COLUMN_NAME');
        $this->model_app_name = $this->curd_config['global']['common_model'] ? 'common' : 'admin';

        $this->set_mid_file_name();
        $this->set_c_file_name();
        $this->set_controller_param();
        $this->set_model_param();
        $this->set_js_param();
        $this->set_html_param();
    }

    /**
     * 格式化数据库的数据
     * @return mixed
     */
    protected function format_info(){
        $field_list = json_decode($this->info['field_list'], true);
        $global = json_decode($this->info['global'], true);
        $curd_config['table_name'] = $this->info['table_name'];
        $curd_config['table_comment'] = $this->info['table_comment'];
        $curd_config['field_list'] = $field_list;
        $curd_config['global'] = $global;
        $curd_config['relation_model'] = json_decode($this->info['relation_model'], true);
        return $curd_config;
    }

    public function create(){
        $this->c_html();
        $this->c_js();
        $this->c_controller();
        $this->c_model();
    }

    /**
     * 更新最后生成时间和生成次数
     */
    public function update_exec_time(){
        //更新时间和次数
        if(!$this->info['exec_create_time']){
            $save['exec_create_time'] = date('Y-m-d H:i:s');
        }
        $save['exec_update_time'] = date('Y-m-d H:i:s');
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
            mkdir(dirname($pathname), 0777, true);
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
        return __DIR__ . DS . 'CurdCategory' . DS . $name . '.lt';
    }

    /**
     * 设置当前生成的类路径名称
     */
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
        $this->html_recycle_c_file_name = app()->getAppPath() . 'admin' . DS . 'view' . DS . strtolower($this->mid_name) . DS . 'recycle.html';
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
        $data['has_soft_del'] = (isset($this->field_list_map['delete_time'])) ? "\n\tpublic \$has_soft_del=1;//是否拥有软删除功能" : "\n\tpublic \$has_soft_del=0;//是否拥有软删除功能";
        $data['sort_order'] = (isset($this->curd_config['global']['sort_field'])) ? "\n\t\t\$order['{$this->curd_config['global']['sort_field']}'] = 'desc';" : "";
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

    /*
     * 下划线转驼峰
     */
    protected function convertUnderline($str)
    {
        $str = preg_replace_callback('/([-_]+([a-z]{1}))/i', function ($matches) {
            return strtoupper($matches[2]);
        }, $str);
        return $str;
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
        $cols = "{type:'checkbox'}\n\t\t\t\t,";
        $cols .= "{field: 'id', title: 'ID',align:'center',width:80}";
        $cols .= "\t\t\t\t,{field: '{$this->curd_config['global']['name_field']}', title: '分类名'}";
        if(isset($this->curd_config['global']['sort_field']) && $this->curd_config['global']['sort_field']){
            $cols .= "\t\t\t\t,{field: '{$this->curd_config['global']['sort_field']}', title: '排序',align:'center',width:100}";
        }
        $temp = "\t\t\t\t,{field:'operation',title:'操作',align:'center',toolbar:'#operation',width:100,fixed:'right'}";
        $cols .= $temp;
        if(isset($this->field_list_map['delete_time'])){
            $data['batch_del'] = ",{
            action: 'del'
            ,title: '删除'
            ,icon: \"layui-icon-delete\"
            ,node: module + \"/\" + controller + \"/del\"
            ,switch_type: \"confirm_action\"
        }";
        }else{
            $data['batch_del'] = '';
        }
        $data['cols'] = $cols;
        $this->recycle_cols = $cols;
        $data['cellMinWidth'] = 180;
        $this->jsParam = ['tpl_name'=>$tpl_name,'data'=>$data,'c_file_name'=>$this->js_c_file_name];
    }

    protected function set_html_param(){
        $index_tpl_name = 'html' . DS . 'index';
        $index_data['jsFileName'] = strtolower(strtolower($this->mid_name));
        $index_data['searchForm'] = $this->get_search_form_group($this->get_replaced_tpl('html' . DS . 'search', ['field_name'=>$this->curd_config['global']['name_field']]));
        $this->htmlIndexParam = ['tpl_name'=>$index_tpl_name,'data'=>$index_data,'c_file_name'=>$this->html_index_c_file_name];

        $add_data = ['parent_field'=>$this->curd_config['global']['parent_field'], 'field_name'=>$this->curd_config['global']['name_field']];
        if(isset($this->curd_config['global']['sort_field']) && $this->curd_config['global']['sort_field']){
            $add_data['sort'] = "<div class=\"layui-form-item\">
        <label class=\"layui-form-label\">排序</label>
        <div class=\"layui-input-block\">
            <input type=\"text\" name=\"row[{$this->curd_config['global']['sort_field']}]\" lay-verify=\"number\" value='0' placeholder=\"请输入排序值\" class=\"layui-input\" lay-verType=\"tips\">
        </div>
    </div>";
        }else{
            $add_data['sort'] = "";
        }
        $add_tpl_name = 'html' . DS . 'add';
        $this->htmlAddParam = ['tpl_name'=>$add_tpl_name,'data'=>$add_data,'c_file_name'=>$this->html_add_c_file_name];

        $edit_tpl_name = 'html' . DS . 'edit';
        $edit_data = ['parent_field'=>$this->curd_config['global']['parent_field'], 'field_name'=>$this->curd_config['global']['name_field']];
        if(isset($this->curd_config['global']['sort_field']) && $this->curd_config['global']['sort_field']){
            $edit_data['sort'] = "<div class=\"layui-form-item\">
        <label class=\"layui-form-label\">排序</label>
        <div class=\"layui-input-block\">
            <input type=\"text\" name=\"row[{$this->curd_config['global']['sort_field']}]\" lay-verify=\"number\" value=\"{\${$this->curd_config['global']['sort_field']}}\" placeholder=\"请输入排序值\" class=\"layui-input\" lay-verType=\"tips\">
        </div>
    </div>";
        }else{
            $edit_data['sort'] = "";
        }
        $this->htmlEditParam = ['tpl_name'=>$edit_tpl_name,'data'=>$edit_data,'c_file_name'=>$this->html_edit_c_file_name];

        $recycle_tpl_name = 'html' . DS . 'recycle';
        $recycle_data['cols'] = $this->recycle_cols;
        $this->htmlRecycleParam = ['tpl_name'=>$recycle_tpl_name,'data'=>$recycle_data,'c_file_name'=>$this->html_recycle_c_file_name];
    }

    protected function get_search_form_group($content)
    {
        return <<<EOD
    <div class="layui-inline" style="padding-left: 10px;">
                <label class="layui-form-label" title="分类名称">分类名称</label>
                <div class="layui-input-inline">
                    {$content}
                </div>
            </div>
EOD;
    }

    //生成controller层
    protected function c_controller(){
        $this->write_to_file($this->controllerParam['tpl_name'], $this->controllerParam['data'], $this->controllerParam['c_file_name']);
    }

    //生成model层文件
    protected function c_model(){
        //是否拥有软删除功能
        $this->modelParam['data']['soft_del_package'] = "";
        $this->modelParam['data']['use_soft_del'] = "";
        $this->modelParam['data']['defaultSoftDelete'] = "";
        if(isset($this->field_list_map['delete_time'])){
            $this->modelParam['data']['soft_del_package'] = "\nuse think\model\concern\SoftDelete;";
            $this->modelParam['data']['use_soft_del'] = "\n\tuse SoftDelete;";
            if($this->field_list_map['delete_time']['COLUMN_DEFAULT'] !== null){
                $this->modelParam['data']['defaultSoftDelete'] = "\n\tprotected \$defaultSoftDelete='{$this->field_list_map['field_list_map']['COLUMN_DEFAULT']}';";
            }
        }
        $this->write_to_file($this->modelParam['tpl_name'], $this->modelParam['data'], $this->modelParam['c_file_name']);
    }

    //生成js文件
    protected function c_js(){
        $this->write_to_file($this->jsParam['tpl_name'], $this->jsParam['data'], $this->jsParam['c_file_name']);
    }

    //生成html模板文件
    protected function c_html(){
        $this->write_to_file($this->htmlIndexParam['tpl_name'], $this->htmlIndexParam['data'], $this->htmlIndexParam['c_file_name']);
        $this->write_to_file($this->htmlAddParam['tpl_name'], $this->htmlAddParam['data'], $this->htmlAddParam['c_file_name']);
        $this->write_to_file($this->htmlEditParam['tpl_name'], $this->htmlEditParam['data'], $this->htmlEditParam['c_file_name']);
        //是否拥有软删除功能
        if(isset($this->field_list_map['delete_time'])){
            $this->write_to_file($this->htmlRecycleParam['tpl_name'], $this->htmlRecycleParam['data'], $this->htmlRecycleParam['c_file_name']);
        }
    }
}