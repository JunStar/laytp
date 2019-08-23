<?php
namespace app\admin\controller;

use controller\Backend;
use think\Exception;
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

            $arr_res = json_decode($res, true);

            foreach($arr_res['data']['list']['data'] as $k=>$v){
                $info = \app\admin\services\Addons::getAddonInfo($v['name']);
                if(!$info){
                    $arr_res['data']['list']['data'][$k]['addon_exist'] = false;
                }else{
                    $arr_res['data']['list']['data'][$k]['addon_exist'] = true;
                }
                $arr_res['data']['list']['data'][$k]['local_state'] = $info['state'];
            }

            $res = json_encode($arr_res);

            $response = Response::create($res);
            throw new HttpResponseException($response);
        }

        $res = json_decode( request_by_curl($get_data_ajax_url), true );
        if(!$res['code']){
            $assign['category'] = [];
            $assign['list'] = [];
        }else{
            $assign['category'] = $res['data']['category'];

            foreach($res['data']['list']['data'] as $k=>$v){
                $info = \app\admin\services\Addons::getAddonInfo($v['name']);
                if(!$info){
                    $arr_res['data']['list']['data'][$k]['addon_exist'] = false;
                }else{
                    $arr_res['data']['list']['data'][$k]['addon_exist'] = true;
                }
                $res['data']['list'][$k]['local_state'] = $info['state'];
            }

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

    //设置状态
    public function set_status(){
        $field_val = $this->request->param('field_val');
        $name = $this->request->param('name');
        try{
            $info['state'] = $field_val;
            if( \app\admin\services\Addons::setAddonInfo($name, $info) ){
                return $this->success('操作成功');
            }else{
                return $this->error('操作失败');
            }
        }catch (Exception $e){
            return $this->error($e->getMessage());
        }
    }
}