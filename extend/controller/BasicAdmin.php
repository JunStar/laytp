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

    /**
     * 渲染js全局变量
     */
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

    /**
     * 生成查询条件
     * @return array
     */
    public function build_params(){
        $where = [];
        $search_param = $this->request->post('search_param');
        if( $search_param ){
            foreach($search_param as $k=>$v){
                if( $v['field'] && $v['condition'] && $v['field_val'] ){
                    if( $v['condition'] == 'like' ){
                        $v['field_val'] = '%'.$v['field_val'].'%';
                    }
                    $where[] = [$v['field'],$v['condition'],$v['field_val']];
//                    if( $v['condition'] == 'time' ){
//                        $where[] = [$v['field'],$v['condition'],'%'.$v['field_val'].'%'];
//                    }
                }
            }
        }
        return $where;
    }
}