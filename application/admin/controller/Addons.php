<?php
namespace app\admin\controller;

use controller\Backend;
use library\Http;
use think\Exception;
use think\exception\HttpResponseException;
use think\facade\Config;
use think\facade\Cookie;
use think\facade\Env;
use think\facade\Response;

class Addons extends Backend
{
    public $addons_service;
    public function initialize(){
        parent::initialize();
        $this->addons_service = new \app\admin\service\Addons();
    }

    //展示插件列表
    public function index(){
        $get_data_ajax_url = Config::get('addons.api_url')."/api/addons/index";
        if($this->request->isAjax()){
            $post['category_id'] = $this->request->param('category_id');
            $charge_type = $this->request->param('charge_type');
            $post['page'] = intval($this->request->param('page'));
            $post['limit'] = intval($this->request->param('limit'));
            if($charge_type == '4'){
                $post['charge_type'] = '';
                $local_addons = $this->addons_service->_info->getAddonsInfo();
                foreach($local_addons as $k=>$v){
                    $local_addons[$k]['title'] = $local_addons[$k]['name'];
                    $local_addons[$k]['addon_exist'] = true;
                    $local_addons[$k]['charge_type'] = 4;
                    $local_addons[$k]['download_num'] = 0;
                    $local_addons[$k]['local_state'] = 1;
                    $config = $this->addons_service->_config->getConfig($v['name']);
                    $local_addons[$k]['config'] = $config ? true : false;
                    $local_addons[$k]['domain'] = isset($local_addons['domain']) ? $local_addons['domain'] : '';
                    $local_addons[$k]['backend_url'] = isset($v['backend_url']) && $v['backend_url'] ? $this->addons_service->_info->getUrl($v['name'],$v['backend_url'],$local_addons[$k]['domain']) : '';
                    $local_addons[$k]['frontend_url'] = isset($v['frontend_url']) && $v['frontend_url'] ? $this->addons_service->_info->getUrl($v['name'],$v['frontend_url'],$local_addons[$k]['domain']) : '';
                    $local_addons[$k]['api_module'] = isset($v['api_module']) && $v['api_module'] ? $v['api_module'] : '';
                }
                $return['data']['list']['data'] = $local_addons;
                $return['data']['list']['total'] = count($local_addons);
                $res = json_encode($return);

                $response = Response::create($res);
                throw new HttpResponseException($response);
            }else{
                $post['charge_type'] = $charge_type;
                $res = Http::post($get_data_ajax_url,$post);

                $arr_res = json_decode($res, true);

                foreach($arr_res['data']['list']['data'] as $k=>$v){
                    $info = $this->addons_service->_info->getAddonInfo($v['name']);
                    $config = $this->addons_service->_config->getConfig($v['name']);
                    if(!$info){
                        $arr_res['data']['list']['data'][$k]['addon_exist'] = false;
                        $arr_res['data']['list']['data'][$k]['local_state'] = 0;
                    }else{
                        $arr_res['data']['list']['data'][$k]['addon_exist'] = true;
                        $arr_res['data']['list']['data'][$k]['local_state'] = $info['state'];
                    }
                    $arr_res['data']['list']['data'][$k]['latest_version'] = $arr_res['data']['list']['data'][$k]['versions'][0]['version'];
                    $arr_res['data']['list']['data'][$k]['domain'] = isset($info['domain']) ? $info['domain'] : '';
                    $arr_res['data']['list']['data'][$k]['backend_url'] = isset($info['backend_url']) && $info['backend_url'] ? $this->addons_service->_info->getUrl($info['name'],$info['backend_url'],$arr_res['data']['list']['data'][$k]['domain']) : '';
                    $arr_res['data']['list']['data'][$k]['frontend_url'] = isset($info['frontend_url']) && $info['frontend_url'] ? $this->addons_service->_info->getUrl($info['name'],$info['frontend_url'],$arr_res['data']['list']['data'][$k]['domain']) : '';
                    $arr_res['data']['list']['data'][$k]['api_module'] = isset($info['api_module']) && $info['api_module'] ? $info['api_module'] : '';
                    $arr_res['data']['list']['data'][$k]['version'] = isset($info['version']) && $info['version'] ? $info['version'] : '';
                    $arr_res['data']['list']['data'][$k]['config'] = $config ? true : false;
                }
                $res = json_encode($arr_res);

                $response = Response::create($res);
                throw new HttpResponseException($response);
            }
        }

        $res = json_decode( Http::post($get_data_ajax_url), true );
        if(!$res['code']){
            $assign['category'] = [];
            $assign['list'] = [];
        }else{
            $assign['category'] = $res['data']['category'];

            foreach($res['data']['list']['data'] as $k=>$v){
                $info = $this->addons_service->_info->getAddonInfo($v['name']);
                if(!$info){
                    $arr_res['data']['list']['data'][$k]['addon_exist'] = false;
                    $res['data']['list']['data'][$k]['local_state'] = 0;
                }else{
                    $arr_res['data']['list']['data'][$k]['addon_exist'] = true;
                    $res['data']['list']['data'][$k]['local_state'] = $info['state'];
                }
            }

            $assign['list'] = $res['data']['list']['data'];

        }
        $this->assign($assign);
        return $this->fetch();
    }

    /**
     * 安装
     */
    public function install()
    {
        if($this->request->isAjax()){
            $name = $this->request->param("name");
            $force = (int)$this->request->param("force");
            if (!$name) {
                $this->error('参数name不能为空');
            }
            try {
                $token = $this->request->param("laytp_token");
                $version = $this->request->param("version");
                $extend = [
                    'token'     => $token,
                    'version'   => $version
                ];
                $installRes = $this->addons_service->install($name, $force, $extend);
                if(!$installRes){
                    $this->error($this->addons_service->getError());
                }
                $info = $this->addons_service->_info->getAddonInfo($name);
                $this->success('安装成功', ['addon' => $info,'reload'=>true]);
            } catch (Exception $e) {
                $this->error($e->getMessage(), $e->getCode());
            }
        }
        $assign['laytp_token'] = $this->request->param("laytp_token");
        $assign['name'] = $this->request->param("name");
        $assign['version'] = $this->request->param("version");
        $this->assign($assign);
        return $this->fetch();
    }

    //设置状态
    public function set_status(){
        $field_val = $this->request->param('field_val');
        $field = $this->request->param('field');
        $name = $this->request->param('name');
        try{
            $info = $this->addons_service->_info->getAddonInfo($name);
            if(!$info){
                return $this->error('插件未安装');
            }
            //打开
            if($field_val == 1){
                $this->addons_service->_menu->enable($info['menu_ids']);
            }
            //关闭
            else{
                $this->addons_service->_menu->disable($info['menu_ids']);
            }
            $info['state'] = $field_val;
            if( $this->addons_service->_info->setAddonInfo($name, $info) ){
                return $this->success('操作成功');
            }else{
                return $this->error('操作失败');
            }
        }catch (Exception $e){
            return $this->error($e->getMessage());
        }
    }

    //卸载
    public function uninstall(){
        $name = $this->request->param("name");
        if (!$name) {
            return $this->error('参数name不能为空');
        }
        try {
            $info = $this->addons_service->_info->getAddonInfo($name);
            if(!$info){
                return $this->error('插件未安装');
            }
            if($info['state'] == 1){
                return $this->error('请先关闭插件');
            }
            if($this->addons_service->uninstall($name)){
                return $this->success('卸载成功',['reload'=>true]);
            }else{
                return $this->error($this->addons_service->getError());
            }
        } catch (Exception $e) {
            $data['file'] = $e->getFile();
            $data['msg'] = $e->getMessage();
            $data['line'] = $e->getLine();
            $data['code'] = $e->getCode();
            return $this->error('卸载失败', $data);
        }
    }

    //配置项
    public function config(){
        $name = $this->request->param('name');
        $this->assign('addon_name', $name);
        if($this->request->isAjax()){
            try{
                $addons = Config::get('addons.');
                $config_items = $this->request->param('row');
                foreach($config_items as $k=>$v){
                    if(is_array($v)){
                        $temp = [];
                        foreach($v['key'] as $arr_k=>$arr_v){
                            $temp[$arr_v] = $v['value'][$arr_k];
                        }
                        $config_items[$k] = $temp;
                    }
                }
                $addons[$name] = $config_items;
                $file_name = Env::get('root_path') .  DS . 'config' . DS . 'addons.php';
                file_put_contents($file_name,"<?php\nreturn ".var_export($addons,true).';');
                return $this->success('配置成功');
            }catch (Exception $e){
                $this->error($e->getMessage());
            }
        }
        $config_items = $this->addons_service->_config->getConfig($name);
        $this->assign('config_items', $config_items);
        $this->assign('config', Config::get('addons.'.$name));
        if(isset($config_items['group']) && $config_items['group']){
            return $this->fetch('group_config');
        }else{
            return $this->fetch();
        }
    }

    //离线安装
    public function off_line_install(){
        set_time_limit(0);
        try{
            $file = $this->request->file('file'); // 获取上传的文件
            if(!$file){
                $this->error('请选择需要的上传文件');
            }

            $upload = Config::get('laytp.upload');
            preg_match('/(\d+)(\w+)/', $upload['maxsize'], $matches);
            $type = strtolower($matches[2]);
            $typeDict = ['b' => 0, 'k' => 1, 'kb' => 1, 'm' => 2, 'mb' => 2, 'gb' => 3, 'g' => 3];
            $size = (int)$upload['maxsize'] * pow(1024, isset($typeDict[$type]) ? $typeDict[$type] : 0);

            $fileInfo = $file->getInfo();

            if(!strstr($fileInfo['type'],'zip')){
                $this->error('上传失败','仅允许上传zip文件');
            }

            $info = $file->validate(['size' => $size])->move(Env::get('root_path') .'addons',false); // 移动文件到指定目录 没有则创建

            if($info->getError()){
                $this->error('上传失败',$info->getError());
            }else{
                $add['file_type'] = $this->request->param('accept');
                $save_name = str_replace('\\','/',$info->getSaveName());
                $add['file_path'] = '/uploads/'.$save_name;
                model('Attachment')->create($add);
                $pathinfo = pathinfo($info->getSaveName());
                \library\Addons::off_line_install($pathinfo['filename']);
                $this->success('安装成功');
            }
        }catch (Exception $e){
            $this->error('安装失败',$e->getMessage());
        }
    }

    //用户信息
    public function user(){
        $this->assign('addons_api_url',Config::get('addons.api_url'));
        return $this->fetch();
    }

    //Api文档
    public function api(){
        $name = $this->request->param('name');
        if($this->request->isAjax()){
            $exec_res = exec_command('app\admin\command\Api',['--output=addons/'.$name.'/api.html', '--title=插件 '.$name.' Api文档','--addon='.$name]);
            if($exec_res['code']){
                $this->success('操作成功');
            }else{
                $this->error($exec_res['msg']);
            }
        }
        $this->assign('name',$name);
        return $this->fetch();
    }

    //域名配置
    public function domain(){
        $name = $this->request->param('name');
        $info = $this->addons_service->_info->getAddonInfo($name);
        if($this->request->isAjax()){
            $info['domain'] = $this->request->param('domain');
            $this->addons_service->_info->setAddonInfo($name,$info);
            $this->success('操作成功');
        }
        $this->assign('name',$name);
        $this->assign('domain',isset($info['domain']) ? $info['domain'] : '');
        return $this->fetch();
    }
}