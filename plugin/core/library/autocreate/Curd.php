<?php

namespace plugin\core\library\autocreate;

use laytp\traits\Error;
use plugin\core\model\autocreate\curd\Field;
use plugin\core\model\autocreate\curd\Table;
use plugin\core\model\Migrations;
use think\facade\Config;

class Curd
{
    use Error;
    protected
        $tableId, //要生成的表id
        $database, //要生成的表名链接数据库的标识
        $tableName, //要生成的表名
        $tableComment, //要生成的表注释
        $engine, //要生成的表存储引擎
        $collation, //要生成的表字符集
        $isHidePk, //是否隐藏主键列.2=不隐藏,1=隐藏
        $isCreateNumber, //是否生成序号列.2=不生成,1=生成
        $fields, //字段列表
        $midName, //中间名称，比如表名为lt_test_a_b那么这里的midName就是/test/a/B,拼接控制器和模型文件的路径和namespace都需要用到
        $controllerModelClassName, //控制器和模型的类名
        $migrationClassName, //要生成的数据迁移文件类名
        $migrationFileName, //要生成的数据迁移文件名
        $controllerFileName, //要生成的控制器文件名
        $modelFileName, //要生成的模型文件名
        $jsFileName, //要生成的js文件名
        $htmlIndexFileName, //要生成的首页html文件名
        $htmlAddFileName, //要生成的添加html文件名
        $htmlEditFileName, //要生成的编辑html文件名
        $htmlRecycleFileName, //要生成的回收站html文件名
        $migrationParam, //生成数据迁移文件，模板需要用到的参数数组
        $controllerParam, //生成controller文件，模板需要用到的参数数组
        $modelParam, //生成model文件，模板需要用到的参数数组
        $jsParam //生成js文件，模板需要用到的参数数组
    ;

    public function __construct($tableId)
    {
        $this->tableId = $tableId;
    }

    public function execute()
    {
        $table = Table::find($this->tableId);
        if (!$table) {
            $this->setError('table_id参数错误');
            return false;
        }

        //这里来删除lt_migrations表对当前表的记录
        $this->database = $table->database;
        $this->tableName = $table->table;
        $this->tableComment = $table->comment;
        $this->engine = $table->engine;
        $this->collation = $table->collation;
        $this->isHidePk = $table->is_hide_pk;
        $this->isCreateNumber = $table->is_create_number;

        $this->fields = Field::where('table_id', '=', $this->tableId)->select();
        if (!$this->fields) {
            $this->setError($this->tableName . '还没有字段，请先添加字段');
            return false;
        }


        $this->setParam();
        $this->create();
        return true;
    }

    /**
     * 设置参数，待生成
     */
    public function setParam()
    {
        $this->setMidFileName();
        $this->setMigrationFileName();
        $this->setFileName();
        $this->setMigrationParam();
        $this->cleanMigration();
        $this->setControllerParam();
        $this->setModelParam();
        $this->setJsParam();
//        $this->set_html_param();
    }

    /**
     * 生成静态文件、控制器和模型文件
     */
    public function create()
    {
        $this->createMigration();
        $this->createController();
        $this->createModel();
        $this->createJs();
    }

    /**
     * 设置当前生成的类路径名称
     */
    protected function setMidFileName()
    {
        $arrTable = explode('_', $this->tableName);
        $basename = ucfirst($arrTable[count($arrTable) - 1]);
        $this->controllerModelClassName = $basename;
        array_shift($arrTable);
        array_pop($arrTable);
        if (count($arrTable)) {
            $strTable = implode(DS, $arrTable) . '/' . $basename;
        } else {
            $strTable = $basename;
        }
        $this->midName = $strTable;
    }

    //设置需要生成的数据迁移文件名
    protected function setMigrationFileName()
    {
        $arrTable = explode('_', $this->tableName);
        array_shift($arrTable);
        foreach ($arrTable as $k => $name) {
            $arrTable[$k] = ucfirst($name);
        }
        $this->migrationClassName = implode('', $arrTable);
        $this->migrationFileName = app()->getRootPath() . 'database' . DS . 'migrations' . DS . date('YmdHis') . '_' . lcfirst(implode('', $arrTable)) . '.php';
    }

    //清空migration的记录，并删除旧的数据表
    public function cleanMigration()
    {
        $migrations = new Migrations();
        $migration = $migrations->where('migration_name', '=', ucfirst($this->migrationClassName))->find();
        if ($migration) {
            $migrationFile = app()->getRootPath() . 'database' . DS . 'migrations' . DS . $migration->version . '_' . lcfirst($migration->migration_name) . '.php';
            unlink($migrationFile);
            $migration->delete();
        }
    }

    //设置所有需要生成的文件名
    protected function setFileName()
    {
        $this->controllerFileName = app()->getAppPath() . 'admin' . DS . 'controller' . DS . $this->midName . '.php';
        $this->modelFileName = app()->getAppPath() . 'common' . DS . 'model' . DS . $this->midName . '.php';
        $this->jsFileName = app()->getRootPath() . 'public' . DS . 'static' . DS . 'admin' . DS . 'js' . DS . strtolower($this->midName) . '.js';
        $this->htmlIndexFileName = app()->getAppPath() . 'public' . DS . 'admin' . DS . strtolower($this->midName) . DS . 'index.html';
        $this->htmlAddFileName = app()->getAppPath() . 'public' . DS . 'admin' . DS . strtolower($this->midName) . DS . 'add.html';
        $this->htmlEditFileName = app()->getAppPath() . 'public' . DS . 'admin' . DS . strtolower($this->midName) . DS . 'edit.html';
        $this->htmlRecycleFileName = app()->getAppPath() . 'public' . DS . 'admin' . DS . strtolower($this->midName) . DS . 'recycle.html';
    }

    //设置生成migration需要的参数
    protected function setMigrationParam()
    {
        $tplName = 'migration' . DS . 'base';
        $data['className'] = $this->migrationClassName;
        $data['tableName'] = str_replace(Config::get("database.connections." . Config::get("database.default") . ".prefix"), '', $this->tableName);
        $data['engine'] = $this->engine;
        $data['tableComment'] = $this->tableComment;
        $data['collation'] = $this->collation;
        $fields = '';
        foreach ($this->fields as $field) {
            $fieldData['field'] = $field->field;
            $fieldData['dataType'] = $field->data_type;
            $fieldData['length'] = $field->length;
            $fieldData['null'] = ($field->is_empty == 2) ? false : true;
            $fieldData['default'] = $field->default;
            $fieldData['comment'] = $field->comment;
            $fields .= $this->getReplacedTpl('migration' . DS . 'field', $fieldData) . "\n\t\t\t";
        }
        $data['fields'] = $fields;
        $this->migrationParam = ['tplName' => $tplName, 'data' => $data, 'fileName' => $this->migrationFileName];
    }

    //生成数据迁移文件并执行数据库迁移命令，生成数据表
    protected function createMigration()
    {
        $this->writeToFile($this->migrationParam['tplName'], $this->migrationParam['data'], $this->migrationParam['fileName']);
        sleep(1);
        system('cd ' . app()->getRootPath() . '&& php think migrate:run > /dev/null', $return);
    }

    /**
     * 设置生成controller需要的参数
     * [
     *  'tplName'=>模板名,
     *  'data' => '执行替换模板的key=>value数组',
     *  'fileName' => '要生成的文件名'
     * ]
     */
    protected function setControllerParam()
    {
        $tplName = 'controller' . DS . 'base';
        $data['controllerNamespace'] = str_replace('/', '\\', dirname('app/admin/controller/' . $this->midName));
        $data['tableComment'] = $this->tableComment;
        $data['modelName'] = strtolower(str_replace('/', '_', $this->midName));
        $data['modelClassName'] = $this->controllerModelClassName;
        $data['modelNamespace'] = str_replace('/', '\\', dirname('app/common/model/' . $this->midName));
        $data['controllerClassName'] = $this->controllerModelClassName;
        $data['indexFunction'] = "";
        $data['recycleFunction'] = "";
        $this->controllerParam = ['tplName' => $tplName, 'data' => $data, 'fileName' => $this->controllerFileName];
    }

    //时间选择器，int类型，自动类型转换
    protected function autoTimeFormat()
    {
        $time_set = [];
        $field_list_map = $this->field_list_map;
        foreach ($this->curd_config['field_list'] as $k => $v) {
            if ($v['form_type'] == 'time') {
                if ($field_list_map[$v['field_name']]['DATA_TYPE'] == 'int') {
                    if ($v['form_additional'] == 'datetime') {
                        $time_set[$v['field_name']] = "\n\t\t" . '\'' . $v['field_name'] . '\'  =>  \'timestamp:Y-m-d H:i:s\',';
                    } else if ($v['form_additional'] == 'month') {
                        $time_set[$v['field_name']] = "\n\t\t" . '\'' . $v['field_name'] . '\'  =>  \'timestamp:Y-m\',';
                    } else if ($v['form_additional'] == 'date') {
                        $time_set[$v['field_name']] = "\n\t\t" . '\'' . $v['field_name'] . '\'  =>  \'timestamp:Y-m-d\',';
                    }
                }
            }
        }
        if ($field_list_map['create_time']['DATA_TYPE'] == 'int') {
            $time_set['create_time'] = "\n\t\t" . '\'create_time\'  =>  \'timestamp:Y-m-d H:i:s\',';
        }
        if ($field_list_map['update_time']['DATA_TYPE'] == 'int') {
            $time_set['update_time'] = "\n\t\t" . '\'update_time\'  =>  \'timestamp:Y-m-d H:i:s\',';
        }
        if ($field_list_map['delete_time']['DATA_TYPE'] == 'int') {
            $time_set['delete_time'] = "\n\t\t" . '\'delete_time\'  =>  \'timestamp:Y-m-d H:i:s\',';
        }
        if ($time_set) {
            return 'protected $type = [' . implode('', $time_set) . "\n\t];";
        } else {
            return '';
        }
    }

    //生成controller层
    protected function createController()
    {
        //是否拥有删除功能
//        if (isset($this->curd_config['global']['hide_del']) && $this->curd_config['global']['hide_del']) {
//            $this->controllerParam['data']['has_del'] = "\n\tpublic \$has_del=0;//是否拥有删除功能";
//        } else {
//            $this->controllerParam['data']['has_del'] = "\n\tpublic \$has_del=1;//是否拥有删除功能";
//        }
//        $this->controllerParam['data']['has_soft_del'] = "\n\tpublic \$has_soft_del=0;//是否拥有软删除功能";
//        //是否拥有软删除功能
//        foreach ($this->curd_config['global']['all_fields'] as $k => $v) {
//            if ($v['field_name'] == 'delete_time') {
//                $this->controllerParam['data']['has_soft_del'] = "\n\tpublic \$has_soft_del=1;//是否拥有软删除功能";
//                break;
//            }
//        }

        $this->controllerParam['data']['hasDel'] = "";
        $this->controllerParam['data']['hasSoftDel'] = "";
        $this->controllerParam['data']['arrayConstAssign'] = "";

        $this->writeToFile($this->controllerParam['tplName'], $this->controllerParam['data'], $this->controllerParam['fileName']);
    }

    /**
     * 设置生成model需要的参数
     * [
     *  'tplName'=>模板名,
     *  'data' => '执行替换模板的key=>value数组',
     *  'fileName' => '要生成的文件名'
     * ]
     */
    protected function setModelParam()
    {
        $tplName = 'model' . DS . 'base';
        $arrTableName = explode('_', $this->tableName);
        $tablePrefix = $arrTableName['0'] . '_';
        $data['tableName'] = '';
        if ($tablePrefix != Config::get('database.connections.' . Config::get('database.default') . '.prefix')) {
            $data['tableName'] = 'protected $table = \'' . $this->tableName . '\';';
        }
        $data['tableComment'] = $this->tableComment;
        $data['modelName'] = strtolower(str_replace('/', '_', $this->midName));
        $data['modelClassName'] = $this->controllerModelClassName;
        $data['modelNamespace'] = str_replace('/', '\\', dirname('app/common/model/' . $this->midName));
        $data['relationModel'] = "";//$this->set_relation_model();
        $data['autoTimeFormat'] = "";//$this->autoTimeFormat();
        $data['autoCreateTime'] = "";//$this->set_auto_write_create_time();
        $data['autoUpdateTime'] = "";//$this->set_auto_write_update_time();
        $data['autoDeleteTime'] = "";//$this->set_auto_write_delete_time();
        $this->modelParam = ['tplName' => $tplName, 'data' => $data, 'fileName' => $this->modelFileName];
    }

    protected function createModel()
    {
//        if(!empty($this->model_array_const)){
//            $this->modelParam['data']['arrayConst'] = implode("\n\n", $this->model_array_const);
//        }else{
        $this->modelParam['data']['arrayConst'] = '';
//        }
        //是否拥有软删除功能
        $this->modelParam['data']['softDelPackage'] = "";
        $this->modelParam['data']['useSoftDel'] = "";
        $this->modelParam['data']['defaultSoftDelete'] = "";
//        foreach($this->curd_config['global']['all_fields'] as $k=>$v){
//            if($v['field_name'] == 'delete_time'){
//                $this->modelParam['data']['soft_del_package'] = "\nuse think\model\concern\SoftDelete;";
//                $this->modelParam['data']['use_soft_del'] = "\n\tuse SoftDelete;";
//                if($v['column_default'] !== null){
//                    $this->modelParam['data']['defaultSoftDelete'] = "\n\tprotected \$defaultSoftDelete='{$v['column_default']}';";
//                }
//                break;
//            }
//        }
        $this->writeToFile($this->modelParam['tplName'], $this->modelParam['data'], $this->modelParam['fileName']);
    }

    protected function setJsParam()
    {
        $tplName = 'js';
        $cols = "{type:'checkbox'}\n\t\t\t\t";
        $hasFirstCols = true;//是否已经有了正常的第一行数据
        //是否隐藏主键列
        if (!$this->isHidePk) {
            $hasFirstCols = true;
            $temp = ",{field:'id',title:'ID',align:'center',width:80}\n";
            $cols .= $temp;
        } else {
            $temp = "//,{field:'id',title:'ID',align:'center',width:80}\n";
            $cols .= $temp;
        }
        //是否生成序号列
        if ($this->isCreateNumber) {
            if ($hasFirstCols) {
                $temp = "\t\t\t\t,{field:'layui_number',title:'序号',align:'center',width:80,type:'numbers'}\n";
                $cols .= $temp;
            } else {
                $temp = "\t\t\t\t{field:'layui_number',title:'序号',align:'center',width:80,type:'numbers'}\n";
                $cols .= $temp;
            }
            if (!$hasFirstCols) $hasFirstCols = true;
        }
        foreach ($this->fields as $k => $v) {
            if ($v['show_table'] == 2) continue;
            if (!$hasFirstCols) {
                $hasFirstCols = true;
                $temp = "\t\t\t\t{field:'{$v['field']}',title:'{$v['comment']}'";
            } else {
                $temp = "\t\t\t\t,{field:'{$v['field']}',title:'{$v['comment']}'";
            }
            $temp .= ",align:'center'";
            //列表排序
            if ($v['show_sort'] == 1) {
                $temp .= ",sort:true";
            }
//            if($relation_info = $this->is_relation_key($v['field_name'])){
//                $relation_show_field = explode(',',$relation_info['show_field']);
//                $templet = [];
//                foreach($relation_show_field as $field){
//                    $templet[] = "{{# if(d.".$relation_info['relation_function_name']."){ }}{{d.".$relation_info['relation_function_name'].".".$field."}}{{# }else{ }}-{{# } }}";
//                }
//                $temp .= ",templet:'<div>".implode(',',$templet)."</div>'";
//            }
            //2个选项的单选按钮 渲染成switch模板 3个及3个以上选项单选按钮渲染成status模板
            if ($v['form_type'] == 'radio') {
                $arr = $this->getArrayByString($v['addition']);
                if (count($arr) > 2) {
                    $json_obj = json_encode($this->getArrayByString($v['addition']), JSON_UNESCAPED_UNICODE);
                    $temp .= ",templet:function(d){\n\t\t\t\t\treturn layTp.tableFormatter.status('{$v['field']}',d.{$v['field']},{$json_obj});\n\t\t\t\t}";
                } else if (count($arr) == 2) {
                    $arr = $this->getArrayByString($v['addition']);
                    $keys_arr = array_keys($arr);
                    $values_arr = array_values($arr);
                    $json_arr = [
                        'open' => ['value' => $keys_arr[1], 'text' => $values_arr[1]],
                        'close' => ['value' => $keys_arr[0], 'text' => $values_arr[0]]
                    ];
                    $json_obj = json_encode($json_arr, JSON_UNESCAPED_UNICODE);
                    $temp .= ",templet:function(d){\n\t\t\t\t\treturn layTpForm.tableForm.switch('{$v['field']}',d,{$json_obj});\n\t\t\t\t}";
                }
            }
            //3个及3个以上选项单选按钮 和 单选下拉框渲染成status的模板
            if ($v['form_type'] == 'select' && $v['addition']['single_multi'] == 'single') {
                $json_obj = json_encode($this->getArrayByString($v['addition']['values']), JSON_UNESCAPED_UNICODE);
                $temp .= ",templet:function(d){\n\t\t\t\t\treturn layTp.tableFormatter.status('{$v['field']}',d.{$v['field']},{$json_obj});\n\t\t\t\t}";
            }
            //复选框和多选下拉框渲染成flag的模板
            if ($v['form_type'] == 'checkbox' || ($v['form_type'] == 'select' && $v['addition']['single_multi'] == 'multi')) {
                if ($v['form_type'] == 'checkbox') {
                    $json_obj = json_encode($this->getArrayByString($v['addition']), JSON_UNESCAPED_UNICODE);
                } else {
                    $json_obj = json_encode($this->getArrayByString($v['addition']['values']), JSON_UNESCAPED_UNICODE);
                }
                $temp .= ",templet:function(d){\n\t\t\t\t\treturn layTp.tableFormatter.flag(d.{$v['field_name']},{$json_obj});\n\t\t\t\t}";
            }
            //image模板
            if ($v['form_type'] == 'upload' && $v['addition']['accept'] == 'images') {
                $temp .= ",templet:function(d){\n\t\t\t\t\treturn layTp.tableFormatter.images(d.{$v['field_name']});\n\t\t\t\t}";
            }
            //video模板
            if ($v['form_type'] == 'upload' && $v['addition']['accept'] == 'video') {
                $temp .= ",templet:function(d){\n\t\t\t\t\treturn layTp.tableFormatter.video(d.{$v['field_name']});\n\t\t\t\t}";
            }
            //audio模板
            if ($v['form_type'] == 'upload' && $v['addition']['accept'] == 'audio') {
                $temp .= ",templet:function(d){\n\t\t\t\t\treturn layTp.tableFormatter.audio(d.{$v['field_name']});\n\t\t\t\t}";
            }
            //file模板
            if ($v['form_type'] == 'upload' && $v['addition']['accept'] == 'file') {
                $temp .= ",templet:function(d){\n\t\t\t\t\treturn layTp.tableFormatter.file(d.{$v['field_name']});\n\t\t\t\t}";
            }
            $temp .= "}\n";
            $cols .= $temp;
        }
        $temp = "\t\t\t\t,{field:'operation',title:'操作',align:'center',toolbar:'#operation',width:100,fixed:'right'}";
        $cols .= $temp;
        $data['cols'] = $cols;
//        $this->recycle_cols = $cols;
        $data['cellMinWidth'] = 80;
        $this->jsParam = ['tplName' => $tplName, 'data' => $data, 'fileName' => $this->jsFileName];
    }

    /**
     * 获取一个数组，根据传入的参数，这个参数string的格式类似于:0=游泳,1=下棋,2=游戏,3=乒乓球,4=羽毛,5=跑步,6=爬山,7=美食,default=0;1;2
     * @param $string
     * @return array
     */
    public function getArrayByString($string)
    {
        $items = explode(",", $string);
        $radio_items = [];//待选项数组
        foreach ($items as $k => $v) {
            $temp = explode('=', $v);
            if ($temp[0] != 'default') {
                $radio_items[$temp[0]] = $temp[1];
            }
        }
        return $radio_items;
    }

    //生成js文件
    protected function createJs()
    {
        $this->writeToFile($this->jsParam['tplName'], $this->jsParam['data'], $this->jsParam['fileName']);
    }

    /**
     * 写入到文件
     * @param string $name 模板文件名
     * @param array $data key=>value形式的替换数组
     * @param string $pathname 生成的绝对文件名
     * @return mixed
     */
    protected function writeToFile($name, $data, $pathname)
    {
        foreach ($data as $index => &$datum) {
            $datum = is_array($datum) ? '' : $datum;
        }
        unset($datum);
        $content = $this->getReplacedTpl($name, $data);

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
    protected function getReplacedTpl($name, $data = [])
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
        $tplTrueName = $this->getTplTrueName($name);
        $tpl = file_get_contents($tplTrueName);
        $content = str_replace($search, $replace, $tpl);
        return $content;
    }

    /**
     * 获取模板文件真实路径
     * @param string $name 举例： $name = 'controller' . DS . 'edit';
     * @return string
     */
    protected function getTplTrueName($name)
    {
        return app()->getRootPath() . DS . 'plugin' . DS . 'core' . DS . 'library' . DS . 'autocreate' . DS . 'curd_template' . DS . $name . '.lt';
    }
}