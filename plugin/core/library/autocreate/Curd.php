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
        $this->tableComment = $table->comment;
        $this->engine = $table->engine;
        $this->collation = $table->collation;

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

//        $this->setControllerParam();
//        $this->setModelParam();
//        $this->set_js_param();
//        $this->set_html_param();
    }

    /**
     * 生成静态文件、控制器和模型文件
     */
    public function create()
    {
        $this->createMigration();
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
        $data['tableName'] = $this->tableName;
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

    //生成数据迁移文件
    protected function createMigration()
    {
        $this->writeToFile($this->migrationParam['tplName'], $this->migrationParam['data'], $this->migrationParam['fileName']);
        sleep(1);
        system('cd ' . app()->getRootPath() . '&& php think migrate:run', $return);
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
        $data['controllerClassName'] = $this->controllerModelClassName;
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

    //生成controller层
    protected function createController()
    {
        if (!empty($this->controller_array_const)) {
            $this->controllerParam['data']['arrayConstAssign'] = implode("\n\t\t", $this->controller_array_const);
            $this->controllerParam['data']['arrayConstAssign'] .= "\n\t\t" . '$this->assign($assign);';
        } else {
            $this->controllerParam['data']['arrayConstAssign'] = '';
        }

        //是否拥有删除功能
        if (isset($this->curd_config['global']['hide_del']) && $this->curd_config['global']['hide_del']) {
            $this->controllerParam['data']['has_del'] = "\n\tpublic \$has_del=0;//是否拥有删除功能";
        } else {
            $this->controllerParam['data']['has_del'] = "\n\tpublic \$has_del=1;//是否拥有删除功能";
        }
        $this->controllerParam['data']['has_soft_del'] = "\n\tpublic \$has_soft_del=0;//是否拥有软删除功能";
        //是否拥有软删除功能
        foreach ($this->curd_config['global']['all_fields'] as $k => $v) {
            if ($v['field_name'] == 'delete_time') {
                $this->controllerParam['data']['has_soft_del'] = "\n\tpublic \$has_soft_del=1;//是否拥有软删除功能";
                break;
            }
        }

        $this->writeToFile($this->controllerParam['tpl_name'], $this->controllerParam['data'], $this->controllerParam['c_file_name']);
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