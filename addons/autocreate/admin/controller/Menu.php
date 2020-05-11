<?php
/**
 * 一键生成菜单
 */
namespace addons\autocreate\admin\controller;

use controller\AddonsBackend;
use think\facade\Env;

class Menu extends AddonsBackend
{
    public $model;

    public function initialize(){
        parent::initialize();
        $this->model = new \addons\autocreate\admin\model\Menu();
    }

    //查看
    public function index(){
        if( $this->request->isAjax() ){
            $where = $this->build_params();
            $select_page = $this->request->param('select_page');
            $limit = $select_page ? $this->request->param('pageSize') : $this->request->param('limit');
            $data = $this->model
                ->with(['first_menu','second_menu'])
                ->where($where)->order('id desc')->paginate($limit)->toArray();
            return $select_page ? select_page_data($data) : layui_table_page_data($data);
        }
        return $this->fetch();
    }

    public function import(){
        if($this->request->isAjax()){
            $post = $this->request->post("row/a");
            if(!$post['first_menu_id']){
                return $this->error('请选择所属一级菜单');
            }
            $pid = $post['second_menu_id'] ? $post['second_menu_id'] : $post['first_menu_id'];
            $controller = $post['controller'];
            importRule($controller, $pid);
            $this->model->create($post);
            return $this->success('生成成功');
        }
        return $this->fetch();
    }

    /**
     * 获取控制器列表
     * @internal
     */
    public function get_controller_list()
    {
        $controllerDir = Env::get('app_path') . 'admin' . DS . 'controller' . DS;
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($controllerDir), \RecursiveIteratorIterator::LEAVES_ONLY
        );
        $list = [];
        $search_name = $this->request->param('name');
        foreach ($files as $name => $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $name = str_replace($controllerDir, '', $filePath);
                $name = str_replace(DS, "/", $name);
                if($search_name){
                    if(strstr($name,$search_name)){
                        $list[] = ['id' => $name, 'name' => $name];
                    }
                }else{
                    $list[] = ['id' => $name, 'name' => $name];
                }
            }
        }
        $pageNumber = $this->request->request("page");
        $pageSize = $this->request->request("pageSize");
        return json(['list' => array_slice($list, ($pageNumber - 1) * $pageSize, $pageSize), 'totalRow' => count($list)]);
    }
}