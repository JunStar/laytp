<?php

namespace plugin\core\library\autocreate;

use laytp\traits\Error;
use plugin\core\model\autocreate\curd\Table;
use think\facade\Config;

class Curd
{
    use Error;
    protected
        $tableId, //要生成的表id
        $database, //要生成的表名链接数据库的标识
        $tableName, //要生成的表名
        $tableComment, //要生成的表注释
        $midName, //中间名称，比如表名为lt_test_a_b那么这里的midName就是/test/a/B,拼接控制器和模型文件的路径和namespace都需要用到
        $controllerModelClassName, //控制器和模型的类名
        $controllerFileName, //要生成的控制器文件名
        $modelFileName, //要生成的模型文件名
        $jsFileName, //要生成的js文件名
        $htmlIndexFileName, //要生成的首页html文件名
        $htmlAddFileName, //要生成的添加html文件名
        $htmlEditFileName, //要生成的编辑html文件名
        $htmlRecycleFileName, //要生成的回收站html文件名
        $controllerParam, //生成controller文件，模板需要用到的参数数组
        $modelParam //生成model文件，模板需要用到的参数数组
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
        $this->tableComment = $table->tableComment;

        $this->setParam();
        return true;
    }

    /**
     * 设置参数，待生成
     */
    public function setParam()
    {
        $this->setMidFileName();
        $this->setFileName();
        $this->setControllerParam();
        $this->setModelParam();
//        $this->set_js_param();
//        $this->set_html_param();
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

    /**
     * 设置生成controller需要的参数
     * [
     *  'tplName'=>模板名,
     *  'data' => '执行替换模板的key=>value数组',
     *  'c_file_name' => '要生成的文件名'
     * ]
     */
    protected function setControllerParam()
    {
        $tplName = 'controller' . DS . 'base';
        $data['controllerNamespace'] = str_replace('/', '\\', dirname('app/admin/controller/' . $this->midName));
        $data['tableComment'] = $this->tableComment;
        $data['modelName'] = strtolower(str_replace('/', '_', $this->midName));
        $data['modelClassName'] = $this->controllerModelClassName;
        $data['modelNamespace'] = str_replace('/', '\\', dirname('app/' . $this->model_app_name . '/model/' . $this->midName));
        $data['controllerClassName'] = $this->controller_model_class_name;
//        $data['indexFunction'] = $this->set_relation_index_function_html();
//        $data['recycleFunction'] = $this->set_relation_recycle_function_html();
        $this->controllerParam = ['tplName' => $tplName, 'data' => $data, 'fileName' => $this->controllerFileName];
    }

    /**
     * 设置生成model需要的参数
     * [
     *  'tplName'=>模板名,
     *  'data' => '执行替换模板的key=>value数组',
     *  'c_file_name' => '要生成的文件名'
     * ]
     */
    protected function setModelParam()
    {
        $tplName = 'model' . DS . 'base';
        $arrTableName = explode('_', $this->tableName);
        $tablePrefix = $arrTableName['0'] . '_';
        $data['tableName'] = '';
        if ($tablePrefix != Config::get('database.prefix')) {
            $data['tableName'] = 'protected $table = \'' . $this->tableName . '\';';
        }
        $data['tableComment'] = $this->tableComment;
        $data['modelName'] = strtolower(str_replace('/', '_', $this->midName));
        $data['modelClassName'] = $this->controllerModelClassName;
        $data['modelNamespace'] = str_replace('/', '\\', dirname('app/common/model/' . $this->midName));
//        $data['relationModel'] = $this->set_relation_model();
        $data['autoTimeFormat'] = $this->autoTimeFormat();
//        $data['autoCreateTime'] = $this->set_auto_write_create_time();
//        $data['autoUpdateTime'] = $this->set_auto_write_update_time();
//        $data['autoDeleteTime'] = $this->set_auto_write_delete_time();
        $this->modelParam = ['tplName' => $tplName, 'data' => $data, 'c_file_name' => $this->model_c_file_name];
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
}