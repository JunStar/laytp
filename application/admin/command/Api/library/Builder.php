<?php

namespace app\admin\command\Api\library;

use think\facade\Config;
use think\facade\View;

class Builder
{
    /**
     * @var \think\View
     */
    public $view = null;

    /**
     * parse classes
     * @var array
     */
    protected $classes = [];

    /**
     * @param array $classes
     */
    public function __construct($classes = [])
    {
        $this->classes = array_merge($this->classes, $classes);
        $this->view = new \think\View(Config::get('template'), Config::get('view_replace_str'));
    }

    protected function extractAnnotations()
    {
        $st_output = [];
        foreach ($this->classes as $class)
        {
            $st_output[] = Extractor::getAllClassAnnotations($class);
        }
        return end($st_output);
    }

    public function parse()
    {
        $annotations = $this->extractAnnotations();


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
