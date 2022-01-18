<?php

namespace app\controller\admin;

use app\service\ConfServiceFacade;
use laytp\controller\Backend;
use laytp\library\CommonFun;
use laytp\library\UploadDomain;
use think\facade\Db;

/**
 * 系统配置控制器
 * Class Conf
 * @package app\admin\controller
 */
class Conf extends Backend
{
    protected $model;
    protected $noNeedAuth = ['getGroupConf', 'saveGroupConf'];

    public function _initialize()
    {
        $this->model = new \app\model\admin\Conf();
    }

    /**
     * 获取某个分组下所有的配置项
     */
    public function getGroupConf()
    {
        $group  = $this->request->param('group');
        $return = ConfServiceFacade::groupGet($group, true);
        return $this->success('获取成功', $return);
    }

    /**
     * 保存配置
     */
    public function saveGroupConf()
    {
        $post  = $this->request->post();
        if(isset($post['laytpUploadFile'])){
            unset($post['laytpUploadFile']);
        }
        $group = $post['group'];
        unset($post['group']);
        $formType = $post['form_type'];
        unset($post['form_type']);
        $allConf = [];
        foreach ($post as $key => $value) {
            if (is_array($value)) {
                $temp = [];
                foreach ($value['key'] as $arrK => $arrV) {
                    if($arrV){
                        $temp[$arrV] = $value['value'][$arrK];
                    }
                }
                $value = $temp;
            }
            $conf['group']     = $group;
            $conf['key']       = $key;
            $conf['value']     = $value;
            $conf['form_type'] = $formType[$key];
            $allConf[] = $conf;
        }
        ConfServiceFacade::groupSet($allConf);
        return $this->success('保存成功', $allConf);
    }

    /**
     * 删除某个分组下某个key的配置信息
     *  这个不在配置页面进行调用，后续要做生成工具的时候统一管理所有的key
     */
    public function del()
    {
        $group = $this->request->param('group');
        $key = $this->request->param('key');
        ConfServiceFacade::del($group, $key);
        return $this->success('删除成功');
    }
}