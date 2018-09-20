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

class Curd extends Command
{
    protected function configure(){
        $this->setName('curd')
            ->addOption('id', 'i', Option::VALUE_REQUIRED, 'autocreate_curd pk value', null)
            ->setDescription('Build CURD from table');
    }

    protected function execute(Input $input, Output $output){
        $id = $input->getOption('id') ?: 0;
        $output->info($id);
    }
}