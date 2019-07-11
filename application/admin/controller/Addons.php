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
        $assign['category'] = $res['data']['category'];
        $assign['list'] = $res['data']['list'];
        $this->assign($assign);
        return $this->fetch();
    }

    /**
     * 安装
     */
    public function install()
    {
        $name = $this->request->post("name");
        $force = (int)$this->request->post("force");
        if (!$name) {
            $this->error(__('Parameter %s can not be empty', 'name'));
        }
        try {
            $uid = $this->request->post("uid");
            $token = $this->request->post("token");
            $version = $this->request->post("version");
            $extend = [
                'uid'       => $uid,
                'token'     => $token,
                'version'   => $version
            ];
            \app\admin\service\Addons::install($name, $force, $extend);
            $info = get_addon_info($name);
            $info['config'] = get_addon_config($name) ? 1 : 0;
            $info['state'] = 1;
            $this->success('安装成功', null, ['addon' => $info]);
        } catch (AddonException $e) {
            $this->result($e->getData(), $e->getCode(), __($e->getMessage()));
        } catch (Exception $e) {
            $this->error(__($e->getMessage()), $e->getCode());
        }
    }
}