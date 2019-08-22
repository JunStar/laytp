<?php
namespace app\admin\controller;

use controller\Backend;
use think\exception\HttpResponseException;
use think\facade\Response;

class Addons extends Backend
{
    //展示插件列表
    public function index(){
        $get_data_ajax_url = "http://local.laytpgw.com/api/addons/index";
        if($this->request->isAjax()){
            $post['category_id'] = $this->request->param('category_id');
            $post['charge_status'] = $this->request->param('charge_status');
            $post['page'] = intval($this->request->param('page'));
            $post['limit'] = intval($this->request->param('limit'));
            $res = request_by_curl($get_data_ajax_url, $post);
            $response = Response::create($res);
            throw new HttpResponseException($response);
        }
        $res = json_decode( request_by_curl($get_data_ajax_url), true );
        if(!$res['code']){
            $assign['category'] = [];
            $assign['list'] = [];
        }else{
            $assign['category'] = $res['data']['category'];
            $assign['list'] = $res['data']['list'];
        }
        $this->assign($assign);
        return $this->fetch();
    }

    /**
     * 安装
     */
    public function install()
    {
        $name = $this->request->param("name");
        $force = (int)$this->request->param("force");
        if (!$name) {
            $this->error('参数name不能为空');
        }
        try {
            $uid = $this->request->param("uid");
            $token = $this->request->param("token");
            $version = $this->request->param("version");
            $extend = [
                'uid'       => $uid,
                'token'     => $token,
                'version'   => $version
            ];
            $installRes = \app\admin\services\Addons::install($name, $force, $extend);
            if($installRes['code']){
                $info = \app\admin\services\Addons::getAddonInfo($name);
//                $info['config'] = \app\admin\services\Addons::getAddonConfig($name) ? 1 : 0;
                $info['state'] = 1;
                $this->success('安装成功', ['addon' => $info]);
            }else{
                $this->error($installRes['msg']);
            }
        } catch (Exception $e) {
            $this->error($e->getMessage(), $e->getCode());
        }
    }
}