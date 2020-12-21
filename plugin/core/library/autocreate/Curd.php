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
        $recycleJsFileName, //要生成的回收站的js文件名
        $htmlIndexFileName, //要生成的首页html文件名
        $htmlAddFileName, //要生成的添加html文件名
        $htmlEditFileName, //要生成的编辑html文件名
        $htmlRecycleFileName, //要生成的回收站html文件名
        $migrationParam, //生成数据迁移文件，模板需要用到的参数数组
        $controllerParam, //生成controller文件，模板需要用到的参数数组
        $modelParam, //生成model文件，模板需要用到的参数数组
        $jsParam, //生成js文件，模板需要用到的参数数组
        $recycleJsParam, //生成回收站js文件，模板需要用到的参数数组
        $htmlIndexParam, //生成index.html，模板需要用到的参数数组
        $htmlAddParam, //生成add.html，模板需要用到的参数数组
        $htmlEditParam, //生成edit.html，模板需要用到的参数数组
        $htmlRecycleParam, //生成recycle.html，模板需要用到的参数数组
        $recycleCols //回收站需要的js字段列表
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
        $this->database       = $table->database;
        $this->tableName      = $table->table;
        $this->tableComment   = $table->comment;
        $this->engine         = $table->engine;
        $this->collation      = $table->collation;
        $this->isHidePk       = $table->is_hide_pk;
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
        $this->setControllerParam();
        $this->setModelParam();
        $this->setJsParam();
        $this->setHtmlParam();
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
        $this->createHtml();
    }

    /**
     * 设置当前生成的类路径名称
     */
    protected function setMidFileName()
    {
        $arrTable                       = explode('_', $this->tableName);
        $basename                       = ucfirst($arrTable[count($arrTable) - 1]);
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

    /**
     * 根据表名获取中间名
     * @param $tableName
     * @return string
     */
    protected function getMidName($tableName)
    {
        $arrTable                       = explode('_', $tableName);
        $basename                       = ucfirst($arrTable[count($arrTable) - 1]);
        $this->controllerModelClassName = $basename;
        array_shift($arrTable);
        array_pop($arrTable);
        if (count($arrTable)) {
            $strTable = implode(DS, $arrTable) . '/' . $basename;
        } else {
            $strTable = $basename;
        }
        return $strTable;
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
        $migrations               = new Migrations();
        $migration                = $migrations->where('migration_name', '=', ucfirst($this->migrationClassName))->find();
        if ($migration) {
            $this->migrationFileName = app()->getRootPath() . 'database' . DS . 'migrations' . DS . $migration->version . '_' . lcfirst($migration->migration_name) . '.php';
            $migration->delete();
        } else {
            $this->migrationFileName = app()->getRootPath() . 'database' . DS . 'migrations' . DS . date('YmdHis') . '_' . lcfirst(implode('', $arrTable)) . '.php';
        }
    }

    //设置所有需要生成的文件名
    protected function setFileName()
    {
        $this->controllerFileName  = app()->getAppPath() . 'admin' . DS . 'controller' . DS . $this->midName . '.php';
        $this->modelFileName       = app()->getAppPath() . 'common' . DS . 'model' . DS . $this->midName . '.php';
        $this->jsFileName          = app()->getRootPath() . 'public' . DS . 'static' . DS . 'admin' . DS . 'js' . DS . strtolower($this->midName) . '.js';
        $this->recycleJsFileName   = app()->getRootPath() . 'public' . DS . 'static' . DS . 'admin' . DS . 'js' . DS . strtolower($this->midName) . 'Recycle.js';
        $this->htmlIndexFileName   = app()->getRootPath() . 'public' . DS . 'admin' . DS . strtolower($this->midName) . DS . 'index.html';
        $this->htmlAddFileName     = app()->getRootPath() . 'public' . DS . 'admin' . DS . strtolower($this->midName) . DS . 'add.html';
        $this->htmlEditFileName    = app()->getRootPath() . 'public' . DS . 'admin' . DS . strtolower($this->midName) . DS . 'edit.html';
        $this->htmlRecycleFileName = app()->getRootPath() . 'public' . DS . 'admin' . DS . strtolower($this->midName) . DS . 'recycle.html';
    }

    //设置生成migration需要的参数
    protected function setMigrationParam()
    {
        $tplName              = 'migration' . DS . 'base';
        $data['className']    = $this->migrationClassName;
        $data['tableName']    = str_replace(Config::get("database.connections." . Config::get("database.default") . ".prefix"), '', $this->tableName);
        $data['engine']       = $this->engine;
        $data['tableComment'] = $this->tableComment;
        $data['collation']    = $this->collation;
        $fields               = '';
        foreach ($this->fields as $field) {
            $fieldData['field']    = $field->field;
            $fieldData['dataType'] = $field->data_type;
//            $fieldData['limit'] = $field->limit;
            $fieldData['null'] = ($field->is_empty == 2) ? 0 : 1;
            if (in_array($field->data_type, ['integer', 'biginteger', 'boolean', 'decimal', 'float'])) {
                $field->default = intval($field->default);
            }
            $fieldData['default'] = (!in_array($field->data_type, ['text', 'datetime'])) ? ' \'default\' => \'' . $field->default . '\',' : ' ';
            $fieldData['comment'] = $field->comment;
            if (in_array($field->data_type, ["float", "decimal"])) {
                $fieldData['limitPrecisionScale'] = "'precision' => {$field->precision}, 'scale' => {$field->scale}";
            } else {
                $fieldData['limitPrecisionScale'] = "'limit' => {$field->limit}";
            }
            $fields .= $this->getReplacedTpl('migration' . DS . 'field', $fieldData) . "\n\t\t\t";
        }
        $data['fields']       = $fields;
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
        $tplName                     = 'controller' . DS . 'base';
        $data['controllerNamespace'] = str_replace('/', '\\', dirname('app/admin/controller/' . $this->midName));
        $data['tableComment']        = $this->tableComment;
        $data['modelName']           = strtolower(str_replace('/', '_', $this->midName));
        $data['modelClassName']      = $this->controllerModelClassName;
        $data['modelNamespace']      = str_replace('/', '\\', dirname('app/common/model/' . $this->midName));
        $data['controllerClassName'] = $this->controllerModelClassName;
        $data['indexFunction']       = "";
        $data['recycleFunction']     = "";
        $this->controllerParam       = ['tplName' => $tplName, 'data' => $data, 'fileName' => $this->controllerFileName];
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
//            if ($v['field'] == 'delete_time') {
//                $this->controllerParam['data']['has_soft_del'] = "\n\tpublic \$has_soft_del=1;//是否拥有软删除功能";
//                break;
//            }
//        }

        $this->controllerParam['data']['hasDel']           = "";
        $this->controllerParam['data']['hasSoftDel']       = "";
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
        $tplName           = 'model' . DS . 'base';
        $arrTableName      = explode('_', $this->tableName);
        $tablePrefix       = $arrTableName['0'] . '_';
        $data['tableName'] = '';
        if ($tablePrefix != Config::get('database.connections.' . Config::get('database.default') . '.prefix')) {
            $data['tableName'] = 'protected $table = \'' . $this->tableName . '\';';
        }
        $data['tableComment']   = $this->tableComment;
        $data['modelName']      = strtolower(str_replace('/', '_', $this->midName));
        $data['modelClassName'] = $this->controllerModelClassName;
        $data['modelNamespace'] = str_replace('/', '\\', dirname('app/common/model/' . $this->midName));
        $data['relationModel']  = "";//$this->set_relation_model();
        $data['autoTimeFormat'] = $this->autoTimeFormat();
        $data['append']         = $this->modelAppend();
        $data['getAttrFun']     = $this->getAttrFun();
        $data['autoCreateTime'] = "";//$this->setAutoWriteCreateTime();
        $data['autoUpdateTime'] = "";//$this->setAutoWriteUpdateTime();
        $data['autoDeleteTime'] = "";//$this->setAutoWriteDeleteTime();
        $this->modelParam       = ['tplName' => $tplName, 'data' => $data, 'fileName' => $this->modelFileName];
    }

    //时间选择器，int类型，自动类型转换
    protected function autoTimeFormat()
    {
        $timeSet = [];
        foreach ($this->fields as $k => $v) {
            if ($v['form_type'] == 'laydate' && $v['data_type'] == 'integer') {
                if ($v['addition']['date_type'] == 'datetime') {
                    $timeSet[$v['field']] = "\n\t\t" . '\'' . $v['field'] . '\'  =>  \'timestamp:Y-m-d H:i:s\',';
                } else if ($v['addition']['date_type'] == 'month') {
                    $timeSet[$v['field']] = "\n\t\t" . '\'' . $v['field'] . '\'  =>  \'timestamp:Y-m\',';
                } else if ($v['addition']['date_type'] == 'date') {
                    $timeSet[$v['field']] = "\n\t\t" . '\'' . $v['field'] . '\'  =>  \'timestamp:Y-m-d\',';
                }
            }
        }
        if ($timeSet) {
            return 'protected $type = [' . implode('', $timeSet) . "\n\t];";
        } else {
            return '';
        }
    }

    /**
     * 模型需要新增的属性
     *  目前仅当integer类型的字段存储时间戳时，由于后台需要进行自动时间戳转换，但是有时候接口可能需要使用原始的整型时间戳，此时需要把数据库原始值查询出来
     *  这里我们把数据库的原始值字段增加后缀 _database
     * @return string
     */
    protected function modelAppend()
    {
        $append = [];
        foreach ($this->fields as $k => $v) {
            if ($v['form_type'] == 'laydate' && $v['data_type'] == 'integer') {
                $append[] = '\'' . $v['field'] . '_database\',';
            }
        }
        if ($append) {
            return 'protected $append = [' . implode('', $append) . "];";
        } else {
            return '';
        }
    }

    protected function getAttrFun()
    {
        $fun = [];
        foreach ($this->fields as $k => $v) {
            if ($v['form_type'] == 'laydate' && $v['data_type'] == 'integer') {
                $fun[] = 'public function get' . ucfirst($this->convertUnderline($v['field'])) . 'DatabaseAttr($value, $data)' . "\n\t" .
                    '{' . "\n\t\t" .
                    'return $data[\'' . $v['field'] . '\'] ? strtotime($data[\'' . $v['field'] . '\']) : 0;' . "\n\t" .
                    '}';
            }
        }
        if ($fun) {
            return implode('', $fun) . "\n\t";
        } else {
            return '';
        }
    }

    protected function createModel()
    {
//        if(!empty($this->model_array_const)){
//            $this->modelParam['data']['arrayConst'] = implode("\n\n", $this->model_array_const);
//        }else{
        $this->modelParam['data']['arrayConst'] = '';
//        }
        //是否拥有软删除功能
        $this->modelParam['data']['softDelPackage']    = "";
        $this->modelParam['data']['useSoftDel']        = "";
        $this->modelParam['data']['defaultSoftDelete'] = "";
        foreach ($this->fields as $k => $v) {
            if ($v['field'] == 'delete_time') {
                $this->modelParam['data']['softDelPackage'] = "\nuse think\model\concern\SoftDelete;";
                $this->modelParam['data']['useSoftDel']     = "\n\tuse SoftDelete;";
                break;
            }
        }
        $this->writeToFile($this->modelParam['tplName'], $this->modelParam['data'], $this->modelParam['fileName']);
    }

    protected function setJsParam()
    {
        $tplName      = 'js' . DS . 'index';
        $cols         = "{type:'checkbox',fixed:'left'}\n\t\t\t\t";
        $hasFirstCols = true;//是否已经有了正常的第一行数据
        //是否隐藏主键列
        if ($this->isHidePk != 1) {
            $hasFirstCols = true;
            $temp         = ",{field:'id',title:'ID',align:'center',width:80,fixed:'left'}";
            $cols         .= $temp;
        } else {
            $temp = "//,{field:'id',title:'ID',align:'center',width:80}";
            $cols .= $temp;
        }
        //是否生成序号列
        if ($this->isCreateNumber == 1) {
            if ($hasFirstCols) {
                $temp = "\t\t\t\t,{field:'layui_number',title:'序号',align:'center',width:80,type:'numbers'}";
                $cols .= $temp;
            } else {
                $temp = "\t\t\t\t{field:'layui_number',title:'序号',align:'center',width:80,type:'numbers'}";
                $cols .= $temp;
            }
            if (!$hasFirstCols) $hasFirstCols = true;
        }
        foreach ($this->fields as $k => $v) {
            if ($v['table_show'] == 2) continue;
            if (!$hasFirstCols) {
                $hasFirstCols = true;
                $temp         = "\t\t\t\t{field:'{$v['field']}',title:'{$v['comment']}'";
            } else {
                $temp = "\n\t\t\t\t,{field:'{$v['field']}',title:'{$v['comment']}'";
            }
            if ($v['cell_width']) {
                $temp .= ",width:{$v['cell_width']}";
            }
            $temp .= ",align:'center'";
            //表头排序
            if ($v['is_thead_sort'] == 1) {
                $temp .= ",sort:true";
            }
//            if($relation_info = $this->is_relation_key($v['field'])){
//                $relation_show_field = explode(',',$relation_info['show_field']);
//                $templet = [];
//                foreach($relation_show_field as $field){
//                    $templet[] = "{{# if(d.".$relation_info['relation_function_name']."){ }}{{d.".$relation_info['relation_function_name'].".".$field."}}{{# }else{ }}-{{# } }}";
//                }
//                $temp .= ",templet:'<div>".implode(',',$templet)."</div>'";
//            }
            //2个选项的单选按钮 渲染成switch模板 3个及3个以上选项单选按钮渲染成status模板
            if ($v['form_type'] == 'radio') {
                $items            = $v['addition'];
                $jsonArr['value'] = $items['value'];
                $jsonArr['text']  = $items['text'];
                $jsonObj          = json_encode($jsonArr, JSON_UNESCAPED_UNICODE);
                $temp             .= ",templet:function(d){
                    return layTp.tableFormatter.status('{$v['field']}',d.{$v['field']},{$jsonObj});
                }";
            }
            if ($v['form_type'] == 'switch') {
                $items = $v['addition'];
                $temp  .= ",templet:function(d){
                    return layTpForm.tableForm.switch(\"{$v['field']}\", d, {
                        \"open\": {\"value\": {$items['open_value']}, \"text\": \"{$items['open_text']}\"},
                        \"close\": {\"value\": {$items['close_value']}, \"text\": \"{$items['close_text']}\"}
                    });
                }";
            }
            //3个及3个以上选项单选按钮 和 单选下拉框渲染成status的模板
            if ($v['form_type'] == 'select') {
                $jsonObj = json_encode($v['addition'], JSON_UNESCAPED_UNICODE);
                $temp    .= ",templet:function(d){\n\t\t\t\t\treturn layTp.tableFormatter.status('{$v['field']}',d.{$v['field']},{$jsonObj});\n\t\t\t\t}";
            }
            //复选框渲染成flag的模板
            if ($v['form_type'] == 'checkbox') {
                $jsonObj = json_encode($v['addition'], JSON_UNESCAPED_UNICODE);
                $temp    .= ",templet:function(d){\n\t\t\t\t\treturn layTp.tableFormatter.flag(d.{$v['field']},{$jsonObj});\n\t\t\t\t}";
            }
            //xmSelect，数据来源=data，单选，渲染成status
            if ($v['form_type'] == 'xm_select' && $v['addition']['data_from_type'] == 'data' && $v['addition']['single_multi_type'] == 'single') {
                $jsonArr['value']   = $v['addition']['value'];
                $jsonArr['text']    = $v['addition']['text'];
                $jsonArr['default'] = $v['addition']['default'];
                $jsonObj            = json_encode($jsonArr, JSON_UNESCAPED_UNICODE);
                $temp               .= ",templet:function(d){\n\t\t\t\t\treturn layTp.tableFormatter.status('{$v['field']}',d.{$v['field']},{$jsonObj});\n\t\t\t\t}";
            }
            //xmSelect，数据来源=data，多选，渲染成flag
            if ($v['form_type'] == 'xm_select' && $v['addition']['data_from_type'] == 'data' && $v['addition']['single_multi_type'] == 'multi') {
                $jsonArr['value']   = $v['addition']['value'];
                $jsonArr['text']    = $v['addition']['text'];
                $jsonArr['default'] = $v['addition']['default'];
                $jsonObj            = json_encode($jsonArr, JSON_UNESCAPED_UNICODE);
                $temp               .= ",templet:function(d){\n\t\t\t\t\treturn layTp.tableFormatter.flag(d.{$v['field']},{$jsonObj});\n\t\t\t\t}";
            }
            //image模板
            if ($v['form_type'] == 'upload' && $v['addition']['accept'] == 'images') {
                $temp .= ",templet:function(d){\n\t\t\t\t\treturn layTp.tableFormatter.images(d.{$v['field']});\n\t\t\t\t}";
            }
            //video模板
            if ($v['form_type'] == 'upload' && $v['addition']['accept'] == 'video') {
                $temp .= ",templet:function(d){\n\t\t\t\t\treturn layTp.tableFormatter.video(d.{$v['field']});\n\t\t\t\t}";
            }
            //audio模板
            if ($v['form_type'] == 'upload' && $v['addition']['accept'] == 'audio') {
                $temp .= ",templet:function(d){\n\t\t\t\t\treturn layTp.tableFormatter.audio(d.{$v['field']});\n\t\t\t\t}";
            }
            //file模板
            if ($v['form_type'] == 'upload' && $v['addition']['accept'] == 'file') {
                $temp .= ",templet:function(d){\n\t\t\t\t\treturn layTp.tableFormatter.file(d.{$v['field']});\n\t\t\t\t}";
            }
            $temp .= "}";
            $cols .= $temp;
        }
//        $temp                 = "\t\t\t\t,{field:'operation',title:'操作',align:'center',toolbar:'#default_operation',width:140,fixed:'right'}";
//        $cols                 .= $temp;
        $data['cols']         = $cols;
        $this->recycleCols    = $cols;
        $data['cellMinWidth'] = 100;
        $this->jsParam        = ['tplName' => $tplName, 'data' => $data, 'fileName' => $this->jsFileName];

        $this->recycleJsParam = ['tplName' => 'js' . DS . 'recycle', 'data' => $data, 'fileName' => $this->recycleJsFileName];
    }

    /**
     * 获取一个数组，根据传入的参数，这个参数string的格式类似于:0=游泳,1=下棋,2=游戏,3=乒乓球,4=羽毛,5=跑步,6=爬山,7=美食,default=0;1;2
     * @param $string
     * @return array
     */
    public function getArrayByString($string)
    {
        $items       = explode(",", $string);
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
        $this->writeToFile($this->recycleJsParam['tplName'], $this->recycleJsParam['data'], $this->recycleJsParam['fileName']);
    }

    protected function setHtmlParam()
    {
        $indexTplName            = 'html' . DS . 'index';
        $indexData['jsFileName'] = strtolower(strtolower($this->midName));
        $indexData['tabs']       = '';
        $indexSearchForm         = [];
        $linkageSelectSearchHtml = [];
        $unSearchType            = ['password', 'upload'];
        foreach ($this->fields as $k => $v) {
            if ($v['search_show'] == 2) {
                continue;
            }
            if (!in_array($v['form_type'], $unSearchType)) {
                if (in_array($v['form_type'], ['linkage_select'])) {
                    $linkageSelectSearchHtml[$v['form_type']][$v['addition']['group_name']][] = $this->getSearchFormContent($v);
                } else {
                    $searchItemContent = $this->getSearchFormContent($v);
                    $indexSearchForm[] = $this->getSearchFormItem($v, $searchItemContent);
                }
            }
            if ($v['is_create_tab'] == 1) {
                $items = [];
                if ($v['form_type'] == 'radio') {
                    $items = $v['addition']['value'];
                }
                if ($v['form_type'] == 'select') {
                    $items = $v['addition']['value'];
                }
                $tabsTplName   = 'html' . DS . 'tabs';
                $tabs['field'] = $v['field'];
                $tabList       = [];
                foreach ($items as $i => $j) {
                    $tabList[] = '<li class="layTpClickSearch" data-field="' . $v['field'] . '" data-val="' . $j . '">' . $v['addition']['text'][$i] . '</li>';
                }
                if ($v['form_type'] == 'switch') {
                    $tabList   = [];
                    $tabList[] = '<li class="layTpClickSearch" data-field="' . $v['field'] . '" data-val="' . $v['addition']['close_value'] . '">' . $v['addition']['close_text'] . '</li>';
                    $tabList[] = '<li class="layTpClickSearch" data-field="' . $v['field'] . '" data-val="' . $v['addition']['open_value'] . '">' . $v['addition']['open_text'] . '</li>';
                }
                $tabs['tabList']   = implode("\n\t", $tabList);
                $indexData['tabs'] = "\n\n" . $this->getReplacedTpl($tabsTplName, $tabs);
            }
        }

        if (count($linkageSelectSearchHtml)) {
            foreach ($linkageSelectSearchHtml as $formType => $groupList) {
                foreach ($groupList as $groupName => $item) {
                    $indexSearchForm[] = $this->getLinkageSelectSearchFormGroup($groupName, join("\n\t\t\t\t", $item));
                }
            }
        }

        $indexData['searchForm'] = implode("\n\n\t\t", $indexSearchForm);

        $this->htmlIndexParam = ['tplName' => $indexTplName, 'data' => $indexData, 'fileName' => $this->htmlIndexFileName];

        $addData               = [];
        $editData              = [];
        $recycleData           = [];
        $linkageSelectAddHtml  = [];
        $linkageSelectEditHtml = [];
        foreach ($this->fields as $k => $v) {
            if (in_array($v['form_type'], ['linkage_select'])) {
                if ($v['add_show'] == 1) {
                    $linkageSelectAddHtml[$v['form_type']][$v['addition']['group_name']][] = $this->getFormItem($v, 'add');
                }
                if ($v['edit_show'] == 1) {
                    $linkageSelectEditHtml[$v['form_type']][$v['addition']['group_name']][] = $this->getFormItem($v, 'edit');
                }
            } else if (in_array($v['form_type'], ['plugin_core_user_id'])) {
                $addData[]  = $this->getFormItem($v, 'add');
                $editData[] = $this->getFormItem($v, 'edit');
            } else {
                if ($v['add_show'] == 1) {
                    $addItemContent = $this->getFormItem($v, 'add');
                    $addData[]      = $this->getFormGroup($v['comment'], $addItemContent, $v['is_empty']);
                }
                if ($v['edit_show'] == 1) {
                    $editItemContent = $this->getFormItem($v, 'edit');
                    $editData[]      = $this->getFormGroup($v['comment'], $editItemContent, $v['is_empty']);
                }
            }
        }

        if (count($linkageSelectAddHtml)) {
            foreach ($linkageSelectAddHtml as $form_type => $group_list) {
                foreach ($group_list as $group_name => $item) {
                    $addData[] = $this->getFormGroup($group_name, join("\n\t\t\t", $item), 0);
                }
            }
        }

        if (count($linkageSelectEditHtml)) {
            foreach ($linkageSelectEditHtml as $form_type => $group_list) {
                foreach ($group_list as $group_name => $item) {
                    $editData[] = $this->getFormGroup($group_name, join("\n\t\t\t", $item), 0);
                }
            }
        }

        $addTplName             = 'html' . DS . 'add';
        $addForm['formContent'] = implode("\n\n", $addData);
        $addForm['action']      = '/admin/' . str_replace('/', '.', strtolower($this->midName)) . '/add';
        $this->htmlAddParam     = ['tplName' => $addTplName, 'data' => $addForm, 'fileName' => $this->htmlAddFileName];

        $editTplName             = 'html' . DS . 'edit';
        $editForm['formContent'] = implode("\n\n", $editData);
        $editForm['action']      = '/admin/' . str_replace('/', '.', strtolower($this->midName)) . '/edit';
        $this->htmlEditParam     = ['tplName' => $editTplName, 'data' => $editForm, 'fileName' => $this->htmlEditFileName];

        $recycleTplName            = 'html' . DS . 'recycle';
        $recycleData['searchForm'] = $indexData['searchForm'];
        $recycleData['jsFileName'] = strtolower($this->midName) . 'Recycle.js';
        $this->htmlRecycleParam    = ['tplName' => $recycleTplName, 'data' => $recycleData, 'fileName' => $this->htmlRecycleFileName];
    }

    //生成html模板文件
    protected function createHtml()
    {
        $this->writeToFile($this->htmlIndexParam['tplName'], $this->htmlIndexParam['data'], $this->htmlIndexParam['fileName']);
        $this->writeToFile($this->htmlAddParam['tplName'], $this->htmlAddParam['data'], $this->htmlAddParam['fileName']);
        $this->writeToFile($this->htmlEditParam['tplName'], $this->htmlEditParam['data'], $this->htmlEditParam['fileName']);
        //是否拥有软删除功能
        foreach ($this->fields as $k => $v) {
            if ($v->field == 'delete_time') {
                $this->writeToFile($this->htmlRecycleParam['tplName'], $this->htmlRecycleParam['data'], $this->htmlRecycleParam['fileName']);
                break;
            }
        }
    }

    protected function getFormItem($info, $type = 'add')
    {
        $func = 'get' . ucfirst($this->convertUnderline($info['form_type'])) . 'Html';
        return $this->$func($info, $type);
    }

    /**
     * 获取表单分组数据
     * @param string $field
     * @param string $content
     * @param integer $isEmpty
     * @return string
     */
    protected function getFormGroup($field, $content, $isEmpty)
    {
        if ($isEmpty != 1) {
            $requiredHtml = " <text title=\"必填项\" style=\"color:red;\">*</text>";
        } else {
            $requiredHtml = "";
        }
        return <<<EOD
    <div class="layui-form-item">
        <label class="layui-form-label" title="{$field}">{$field}{$requiredHtml}</label>
        <div class="layui-input-block">
            {$content}
        </div>
    </div>
EOD;
    }

    protected function getSearchFormContent($info)
    {
        $func = 'getSearch' . ucfirst($this->convertUnderline($info['form_type'])) . 'Html';
        return $this->$func($info);
    }

    protected function getSearchFormItem($info, $content)
    {
        $fieldComment = $info['comment'];
        return <<<EOD
<div class="layui-form-item layui-inline">
                <label class="layui-form-label" title="{$fieldComment}">{$fieldComment}</label>
                <div class="layui-input-inline">
                    {$content}
                </div>
            </div>
EOD;
    }

    protected function getLinkageSelectSearchFormGroup($field, $content)
    {
        return <<<EOD
    <div class="layui-form-item layui-inline">
                <label class="layui-form-label" title="{$field}">{$field}</label>
                {$content}
            </div>
EOD;
    }

    /**
     * 接下来是html的各种控件模板内容生成，比如input、select、upload等等
     */

    /**
     * 获取input需要生成的html，在生成add和edit表单的时候可以用到
     * @param $info
     * @param $type string 类型，add或者edit
     * @return string
     */
    protected function getInputHtml($info, $type)
    {
        $name            = 'html' . DS . $type . DS . 'input';
        $data['field']   = $info['field'];
        $data['comment'] = $info['comment'];
        if ($info['is_empty'] == 2) {
            $data['verify'] = $info['addition']['verify'] ? 'required|' . $info['addition']['verify'] : 'required';
        } else {
            $data['verify'] = $info['addition']['verify'];
        }
        return $this->getReplacedTpl($name, $data);
    }

    /**
     * 获取input需要生成的html，在生成搜索表单时用到
     * @param $info
     * @return string
     */
    protected function getSearchInputHtml($info)
    {
        $name            = 'html' . DS . 'search' . DS . 'input';
        $data['field']   = $info['field'];
        $data['comment'] = $info['comment'];
        return $this->getReplacedTpl($name, $data);
    }

    /**
     * admin_id类型模板
     * @param $info
     * @param $type
     * @return string
     */
    protected function getPluginCoreUserIdHtml($info, $type)
    {
        $name            = 'html' . DS . $type . DS . 'admin_id';
        $data['field']   = $info['field'];
        $data['comment'] = $info['comment'];
        return $this->getReplacedTpl($name, $data);
    }

    /**
     * admin_id类型模板
     * @param $info
     * @return string
     */
    protected function getSearchPluginCoreUserIdHtml($info)
    {
        $name            = 'html' . DS . 'search' . DS . 'admin_id';
        $data['field']   = $info['field'];
        $data['comment'] = $info['comment'];
        return $this->getReplacedTpl($name, $data);
    }

    /**
     * 获取input需要生成的html，在生成add和edit表单的时候可以用到
     * @param $info
     * @param $type string 类型，add或者edit
     * @return string
     */
    protected function getTextareaHtml($info, $type)
    {
        $name            = 'html' . DS . $type . DS . 'textarea';
        $data['field']   = $info['field'];
        $data['comment'] = $info['comment'];
        $data['verify']  = $info['is_empty'] == 1 ? '' : 'required';
        return $this->getReplacedTpl($name, $data);
    }

    /**
     * 获取input需要生成的html，在生成搜索表单时用到
     * @param $info
     * @return string
     */
    protected function getSearchTextareaHtml($info)
    {
        $name            = 'html' . DS . 'search' . DS . 'textarea';
        $data['field']   = $info['field'];
        $data['comment'] = $info['comment'];
        return $this->getReplacedTpl($name, $data);
    }

    /**
     * 获取input需要生成的html，在生成add和edit表单的时候可以用到
     * @param $info
     * @param $type string 类型，add或者edit
     * @return string
     */
    protected function getRadioHtml($info, $type)
    {
        $items     = $info['addition'];
        $name      = 'html' . DS . $type . DS . 'radio';
        $radioHtml = '';
        foreach ($items['value'] as $k => $v) {
            $tempData['field']         = $info['field'];
            $tempData['value']         = $items['value'][$k];
            $tempData['title']         = $items['text'][$k];
            $tempData['checkedStatus'] = ($items['value'][$k] == $items['default']) ? 'checked="checked"' : '';
            $radioHtml                 .= $this->getReplacedTpl($name, $tempData) . "\n\t\t\t";
        }
        $radioHtml = rtrim($radioHtml, "\n\t\t\t");
//            $this->set_model_array_const($info['field_name'], $model_array_const);
        return $radioHtml;
    }

    protected function getSearchRadioHtml($info)
    {
        $name          = 'html' . DS . 'search' . DS . 'radio';
        $data['field'] = $info['field'];
        $items         = $info['addition'];
        $options       = '';
        foreach ($items['value'] as $k => $v) {
            $options .= "\t\t\t\t\t\t" . '<option value="' . $items['value'][$k] . '">' . $items['text'][$k] . '</option>' . "\n";
        }
        $options         = "\t\t\t\t" . '<option value=""></option>' . "\n" . rtrim($options, "\n");
        $data['options'] = $options;
        $data['comment'] = $info['comment'];
        return $this->getReplacedTpl($name, $data);
    }

    protected function getSwitchHtml($info, $type)
    {
        $items                  = $info['addition'];
        $name                   = 'html' . DS . $type . DS . 'switch';
        $data['field']          = $info['field'];
        $data['close_value']    = $items['close_value'];
        $data['open_value']     = $items['open_value'];
        $data['checked_status'] = ($items['default_status'] == 'open') ? 'checked="checked"' : '';
        $data['lay_text']       = $items['open_text'] . '|' . $items['close_text'];
//        $this->set_model_array_const($info['field_name'], $model_array_const);
        return $this->getReplacedTpl($name, $data);
    }

    protected function getSearchSwitchHtml($info)
    {
        $name            = 'html' . DS . 'search' . DS . 'radio';
        $data['field']   = $info['field'];
        $items           = $info['addition'];
        $options         = '';
        $options         .= "\t\t\t\t\t\t" . '<option value="' . $items['close_value'] . '">' . $items['close_text'] . '</option>' . "\n";
        $options         .= "\t\t\t\t\t\t" . '<option value="' . $items['open_value'] . '">' . $items['open_text'] . '</option>' . "\n";
        $data['options'] = $options;
        $data['comment'] = $info['comment'];
        return $this->getReplacedTpl($name, $data);
    }

    protected function getSearchCheckboxHtml($info)
    {
        $name          = 'html' . DS . 'search' . DS . 'checkbox';
        $data['field'] = $info['field'];
        $items         = $info['addition'];
        $data['max']   = count($items);
        $optionItems   = [];
//        $model_array_const = [];
        foreach ($items['value'] as $k => $v) {
            $optionItems[] = ['value' => $items['value'][$k], 'name' => $items['text'][$k]];
//            $model_array_const[(string)$temp[0]] = $temp[1];
        }
        $data['source'] = str_replace("\"", "'", json_encode($optionItems, JSON_UNESCAPED_UNICODE));
//        $this->set_controller_array_const($info['field_name'], $model_array_const);
        return $this->getReplacedTpl($name, $data);
    }

    protected function getCheckboxHtml($info, $type)
    {
        $name          = 'html' . DS . $type . DS . 'checkbox';
        $data['field'] = $info['field'];
        $items         = $info['addition'];
        $optionItems   = [];
//        $model_array_const = [];
        $checkboxHtml = '';
        foreach ($items['value'] as $k => $v) {
            $optionItems[] = ['value' => $items['value'][$k], 'text' => $items['text'][$k]];
        }

        $defaultValueArr = isset($items['default']) ? $items['default'] : [];

        foreach ($optionItems as $k => $v) {
            if ($type == 'add') {
                $data['checked'] = in_array($v['value'], $defaultValueArr) ? 'checked="checked"' : '';
            }
            $data['value'] = $v['value'];
            $data['text']  = $v['text'];
            $checkboxHtml  .= $this->getReplacedTpl($name, $data) . "\n\t\t\t";
        }
        $checkboxHtml = rtrim($checkboxHtml, "\n\t\t\t");

//        $this->set_model_array_const($info['field_name'], $model_array_const);
        return $checkboxHtml;
    }

    protected function getSearchSelectHtml($info)
    {
        $name          = 'html' . DS . 'search' . DS . 'radio';
        $data['field'] = $info['field'];
        $items         = $info['addition'];
        $options       = '';
        foreach ($items['value'] as $k => $v) {
            $options .= "\t\t\t\t\t\t" . '<option value="' . $items['value'][$k] . '">' . $items['text'][$k] . '</option>' . "\n";
        }
        $options         = "\t\t\t\t" . '<option value=""></option>' . "\n" . rtrim($options, "\n");
        $data['options'] = $options;
        $data['comment'] = $info['comment'];
        return $this->getReplacedTpl($name, $data);
    }

    /**
     * 获取select需要生成的html，在生成add和edit表单的时候可以用到
     * @param $info
     * @param $type string 类型，add或者edit
     * @return string
     */
    protected function getSelectHtml($info, $type)
    {
        $items     = $info['addition'];
        $name      = 'html' . DS . $type . DS . 'radio';
        $radioHtml = '';
        foreach ($items['value'] as $k => $v) {
            $tempData['field']         = $info['field'];
            $tempData['value']         = $items['value'][$k];
            $tempData['title']         = $items['text'][$k];
            $tempData['checkedStatus'] = ($items['value'][$k] == $items['default']) ? 'checked="checked"' : '';
            $radioHtml                 .= $this->getReplacedTpl($name, $tempData) . "\n\t\t\t";
        }
        $radioHtml = rtrim($radioHtml, "\n\t\t\t");
//            $this->set_model_array_const($info['field_name'], $model_array_const);
        return $radioHtml;
    }

    protected function getSearchXmSelectHtml($info)
    {
        $name          = 'html' . DS . 'search' . DS . 'xm_select';
        $data['field'] = $info['field'];
        if ($info['addition']['data_from_type'] === "data") {
            $data['sourceType'] = "data";
            $source             = [];
            foreach ($info['addition']['value'] as $k => $v) {
                $source[] = ['name' => $info['addition']['text'][$k], 'value' => $info['addition']['value'][$k]];
            }
            $data['source']       = str_replace('"', '\'', json_encode($source, JSON_UNESCAPED_UNICODE));
            $data['sourceTree']   = "false";
            $data['paging']       = "false";
            $data['textField']    = "";
            $data['subTextField'] = "";
            $data['iconField']    = "";
            $data['valueField']   = "";
        } else {
            $data['sourceType'] = "url";
            $tableId            = $info['addition']['table_id'];
            $table              = Table::find($tableId);
            $tableName          = $table->table;
            $midName            = str_replace('/', '.', $this->getMidName($tableName));
            $data['source']     = 'admin/' . $midName . '/index';
            $table_is_tree      = 0;//取到数据表是否为无限极分类模型
            if ($table_is_tree) {
                $data['sourceTree'] = "true";
                $data['paging']     = "false";
            } else {
                $data['sourceTree'] = "false";
                $data['paging']     = "true";
            }
            $data['textField']    = $info['addition']['title_field'];
            $data['subTextField'] = $info['addition']['sub_title_field'];
            $data['iconField']    = $info['addition']['icon_field'];
            $data['valueField']   = "id";
        }
        $data['layVerify'] = ($info['is_empty'] == 1) ? "" : "required";
        $data['comment']   = $info['comment'];
        $data['direction'] = $info['addition']['direction'];
        return $this->getReplacedTpl($name, $data);
    }

    protected function getXmSelectHtml($info, $type)
    {
        $name          = 'html' . DS . $type . DS . 'xm_select';
        $data['field'] = $info['field'];
        if ($info['addition']['data_from_type'] === "data") {
            $data['sourceType'] = "data";
            $source             = [];
            foreach ($info['addition']['value'] as $k => $v) {
                $source[] = ['name' => $info['addition']['text'][$k], 'value' => $info['addition']['value'][$k]];
            }
            $data['source']       = str_replace('"', '\'', json_encode($source, JSON_UNESCAPED_UNICODE));
            $data['sourceTree']   = "false";
            $data['paging']       = "false";
            $data['textField']    = "";
            $data['subTextField'] = "";
            $data['iconField']    = "";
            $data['valueField']   = "";
        } else {
            $data['sourceType'] = "url";
            $tableId            = $info['addition']['table_id'];
            $table              = Table::find($tableId);
            $tableName          = $table->table;
            $midName            = str_replace('/', '.', $this->getMidName($tableName));
            $data['source']     = 'admin/' . $midName . '/index';
            $table_is_tree      = 0;//取到数据表是否为无限极分类模型
            if ($table_is_tree) {
                $data['sourceTree'] = "true";
                $data['paging']     = "false";
            } else {
                $data['sourceTree'] = "false";
                $data['paging']     = "true";
            }
            $data['textField']    = $info['addition']['title_field'];
            $data['subTextField'] = $info['addition']['sub_title_field'];
            $data['iconField']    = $info['addition']['icon_field'];
            $data['valueField']   = "id";
        }
        $data['layVerify'] = ($info['is_empty'] == 1) ? "" : "required";
        $data['comment']   = $info['comment'];
        $data['direction'] = $info['addition']['direction'];
        $data['radio']     = ($info['addition']['single_multi_type'] === 'single') ? "true" : "false";
        $data['max']       = $info['addition']['max'];
        return $this->getReplacedTpl($name, $data);
    }

    public function getSearchLinkageSelectHtml($info)
    {
        $name                    = 'html' . DS . 'search' . DS . 'linkage_select';
        $data['field']           = $info['field'];
        $data['comment']         = $info['comment'];
        $searchTable             = Table::find($info['addition']['table_id']);
        $midName                 = str_replace('/', '.', $this->getMidName($searchTable->table));
        $data['url']             = 'admin/' . $midName . '/index';
        $data['leftField']       = $info['addition']['left_field'];
        $data['rightField']      = $info['addition']['right_field'];
        $data['showField']       = $info['addition']['show_field'];
        $data['searchField']     = $info['addition']['search_field'];
        $data['searchCondition'] = $info['addition']['search_condition'];
        $data['searchVal']       = $info['addition']['search_val'];
        return $this->getReplacedTpl($name, $data);
    }

    public function getLinkageSelectHtml($info, $type)
    {
        $name                    = 'html' . DS . $type . DS . 'linkage_select';
        $data['field']           = $info['field'];
        $data['verify']          = ($info['is_empty'] == 1) ? "" : "required";
        $data['comment']         = $info['comment'];
        $searchTable             = Table::find($info['addition']['table_id']);
        $midName                 = str_replace('/', '.', $this->getMidName($searchTable->table));
        $data['url']             = 'admin/' . $midName . '/index';
        $data['leftField']       = $info['addition']['left_field'];
        $data['rightField']      = $info['addition']['right_field'];
        $data['showField']       = $info['addition']['show_field'];
        $data['searchField']     = $info['addition']['search_field'];
        $data['searchCondition'] = $info['addition']['search_condition'];
        $data['searchVal']       = $info['addition']['search_val'];
        return $this->getReplacedTpl($name, $data);
    }

    public function getSearchLaydateHtml($info)
    {
        $name                = 'html' . DS . 'search' . DS . 'laydate';
        $data['field']       = $info['field'];
        $data['comment']     = $info['comment'];
        $data['laydateType'] = $info['addition']['date_type'];
        if ($info['data_type'] == 'integer') {
            if (in_array($info['form_type'], ['datetime', 'month', 'date'])) {
                $data['searchType'] = 'BETWEEN_STRTOTIME';
            } else {
                $data['searchType'] = 'BETWEEN';
            }
        } else {
            $data['searchType'] = 'BETWEEN';
        }
        return $this->getReplacedTpl($name, $data);
    }

    public function getLaydateHtml($info, $type)
    {
        $name                = 'html' . DS . $type . DS . 'laydate';
        $data['field']       = $info['field'];
        $data['comment']     = $info['comment'];
        $data['laydateType'] = $info['addition']['date_type'];
        $data['verify']      = ($info['is_empty'] == 1) ? "" : "required";
        return $this->getReplacedTpl($name, $data);
    }

    public function getSearchColorPickerHtml()
    {
        return "";
    }

    public function getColorPickerHtml($info, $type)
    {
        $name              = 'html' . DS . $type . DS . 'color_picker';
        $data['field']     = $info['field'];
        $data['comment']   = $info['comment'];
        $data['color']     = $info['addition']['color'];
        $data['predefine'] = $info['addition']['predefine'] ? "true" : "false";
        $data['colors']    = json_encode($info['addition']['colors'], JSON_UNESCAPED_UNICODE);
        $data['alpha']     = (isset($info['addition']['alpha']) && $info['addition']['alpha']) ? "true" : "false";
        $data['format']    = $info['addition']['format'];
        return $this->getReplacedTpl($name, $data);
    }

    public function getSearchUploadHtml()
    {
        return "";
    }

    public function getUploadHtml($info, $type)
    {
        $name            = 'html' . DS . $type . DS . 'upload';
        $data['field']   = $info['field'];
        $data['comment'] = $info['comment'];
        $data['accept']  = $info['addition']['accept'];
        $data['width']   = ($info['addition']['accept'] == 'image' && $info['addition']['width']) ? "\n\t\t\t\t\t\t\t\tdata-width=\"" . $info['addition']['width'] . "\"" : "";
        $data['height']  = ($info['addition']['accept'] == 'image' && $info['addition']['height']) ? "\n\t\t\t\t\t\t\t\tdata-height=\"" . $info['addition']['height'] . "\"" : "";
        $data['multi']   = ($info['addition']['multi'] == 'single') ? "false" : "true";
        $data['max']     = intval($info['addition']['max']);
        $data['dir']     = $info['addition']['dir'];
        $data['url']     = $info['addition']['url'];
        $data['mime']    = $info['addition']['mime'];
        $data['size']    = $info['addition']['size'];
        $data['verify']  = ($info['is_empty'] == 1) ? "" : "required";
        return $this->getReplacedTpl($name, $data);
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
            $search[]  = "{%{$k}%}";
            $replace[] = $v;
        }
        $tplTrueName = $this->getTplTrueName($name);
        $tpl         = file_get_contents($tplTrueName);
        $content     = str_replace($search, $replace, $tpl);
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

    /**
     * 下划线转驼峰
     * @param $str
     * @return string|string[]|null
     */
    protected function convertUnderline($str)
    {
        $str = preg_replace_callback('/([-_]+([a-z]{1}))/i', function ($matches) {
            return strtoupper($matches[2]);
        }, $str);
        return $str;
    }
}