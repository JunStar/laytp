<?php
namespace app\admin\controller;

use controller\Backend;
use think\exception\HttpResponseException;
use think\facade\Response;

class Addons extends Backend
{
    public function index(){
        $get_data_ajax_url = "http://local.laytpgw.com/api/addons/index";
        if($this->request->isAjax()){
            $post['category_id'] = intval($this->request->param('category_id'));
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
}