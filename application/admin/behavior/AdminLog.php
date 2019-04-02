<?php

namespace app\admin\behavior;

class AdminLog
{

    public function run()
    {
//        if (request()->isPost())
//        {
            \app\admin\model\AdminLog::record();
//        }
    }

}
