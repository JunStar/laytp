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
use think\console\Output;

class Curd extends Command
{
    protected function configure(){
        $this->setName('curd')
            ->setDescription('Build CURD from table');
    }

    protected function execute(Input $input, Output $output){
        $output->info('this is create curd command');
        $output->info('this is create curd command');
    }
}