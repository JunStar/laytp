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

        //更新时间
        $info = $autocreateCurdModel->get($id);
        if(!$info['exec_create_time']){
            $save['exec_create_time'] = time();
        }
        $save['exec_update_time'] = time();
        $autocreateCurdModel->where(['id'=>$id])->update($save);

        $output->info('生成成功');
    }
}