<?php

namespace app\api\controller;

use laytp\BaseController;

/**
 * @ApiInternal ()
 */
class Index extends BaseController
{
    public function index()
    {
        return redirect('/api.html');
    }
}