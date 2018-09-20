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