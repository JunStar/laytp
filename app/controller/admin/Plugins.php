<?php

namespace app\controller\admin;

use app\service\admin\PluginsServiceFacade;
use laytp\controller\Backend;
use laytp\library\Http;
use think\facade\Config;

/**
 * 插件管理
 */
class Plugins extends Backend
{
    /**
     * files模型对象
     * @var \app\model\Files
     */
    protected $model;
    public $hasSoftDel=1;//是否拥有软删除功能

    // 获取laytp官网定义的插件分类列表
    public function category()
    {
        $url = Config::get('plugin.apiUrl') . '/plugins/category';
        $data = json_decode(Http::get($url), true)['data'];
        return $this->success('获取成功', $data);
    }

    // 查看
    public function index()
    {
        $params['page'] = $this->request->param('page', 1);
        $params['limit'] = $this->request->param('limit', 10);
        $params['category_id'] = $this->request->param('category_id', 0);
        $url = Config::get('plugin.apiUrl') . '/plugins/index';
        $data = json_decode(Http::get($url, $params), true)['data'];
        $installed = Config::get('plugin.installed');
        foreach($data['data'] as $k=>$datum){
            if(in_array($datum['alias'], $installed)){
                $data['data'][$k]['installed'] = 1;
                $pluginInfo = PluginsServiceFacade::getPluginInfo($datum['alias']);
                if(isset($pluginInfo['version'])){
                    $version = $pluginInfo['version'];
                }else{
                    $version = '1.0.0';
                }
                $data['data'][$k]['version'] = $version;
            }else{
                $data['data'][$k]['installed'] = 2;
            }
        }
        return $this->success('获取成功', $data);
    }

    // 离线安装 - 所有的离线安装都是覆盖安装
    public function offLineInstall()
    {
        if(PluginsServiceFacade::offLineInstall()){
            return $this->success('安装成功');
        }else{
            return $this->error(PluginsServiceFacade::getError());
        }
    }

    // 卸载
    public function uninstall()
    {
        $plugin = $this->request->param('plugin');

        if(PluginsServiceFacade::unInstall($plugin)){
            return $this->success('卸载成功');
        }else{
            return $this->error(PluginsServiceFacade::getError());
        }
    }

    // 安装
    public function install()
    {
        $plugin = $this->request->param('plugin');
        $laytpGwToken = $this->request->param('laytpGwToken');

        if(PluginsServiceFacade::install($plugin, $laytpGwToken)){
            return $this->success('安装成功');
        }else{
            $code = PluginsServiceFacade::getError();
            $msg = '安装遇到错误';
            if($code === 1){
                $msg = '请先登录';
            }
            if($code === 2){
                $msg = '请先购买插件';
            }
            return $this->error($msg, $code);
        }
    }
}