<?php

namespace app\admin\command\Api\library;

use think\facade\Config;
use think\facade\View;

class Builder
{

    public function parse(){

    }

    /**
     * 渲染
     * @param string $template
     * @param array $vars
     * @return string
     */
    public function render($template, $vars = [])
    {
        $docs_list = $this->parse();

        return View::display(file_get_contents($template), array_merge($vars, ['docs_list' => $docs_list]));
    }

}
