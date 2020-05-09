<?php

namespace app\admin\behavior;

class AdminLog
{

    public function run($token)
    {
//        if (request()->isPost())
//        {
            \app\admin\model\AdminLog::record($token);
//        }
    }

}
