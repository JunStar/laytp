<?php
/**
 * 后台基类
 */

namespace controller;

use think\Controller;

class BasicAdmin extends Controller
{
    public function initialize(){
        $module = $this->request->module();
        $controller = strtolower($this->request->controller());
        $action = strtolower($this->request->action());

        $this->js_global_var();
    }

    public function js_global_var(){
        $module = $this->request->module();
        $controller = strtolower($this->request->controller());
        $action = strtolower($this->request->action());

        $assign['js_global_var']['module'] = $module;
        $assign['js_global_var']['controller'] = $controller;
        $assign['js_global_var']['action'] = $action;

        $assign['js_global_var']['current_fun_obj'] = str_replace('.','_',$controller);
        $this->assign($assign);
    }
}