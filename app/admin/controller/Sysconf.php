<?php

namespace app\admin\controller;

use laytp\controller\Backend;
use think\facade\Db;

/**
 * 系统配置控制器
 * Class Sysconf
 * @package app\admin\controller
 */
class Sysconf extends Backend
{
    protected $model;
    public $has_del = 1;//是否拥有删除功能
    public $has_soft_del = 0;//是否拥有软删除功能

    public function _initialize()
    {
        $this->model = new \app\common\model\admin\Sysconf();
    }

    /**
     * 获取分组列表
     */
    public function getGroup(){
        $config = $this->model->where([
            ['group', '=', 'layTpSys'],
            ['key', '=', 'configGroup']
        ])->value('value');
        $layTpSys = json_decode($config, JSON_UNESCAPED_UNICODE);
        unset($layTpSys['layTpSys']);
        return $this->success('获取成功', $layTpSys);
    }

    /**
     * 获取某个分组下所有的配置项
     */
    public function getGroupItem(){
        $group = $this->request->param('group');
        $items = $this->model->where('group', '=', $group)->order('id','asc')->select();
        return $this->success('获取成功', $items);
    }

    /**
     * 添加分组
     * @return \think\response\Json
     */
    public function addGroup()
    {
        $post = filter_post_data($this->request->post());
        $config = $this->model->where([
            ['group', '=', 'layTpSys'],
            ['key', '=', 'configGroup']
        ])->value('value');
        $layTpSys = json_decode($config, JSON_UNESCAPED_UNICODE);
        if(array_key_exists($post['group'], $layTpSys)){
            return $this->error('分组的英文标识已经存在');
        }
        $flipLayTpSys = array_flip($layTpSys);
        if(array_key_exists($post['group_name'], $flipLayTpSys)){
            return $this->error('分组名称已经存在');
        }
        $layTpSys[$post['group']] = $post['group_name'];
        $value = json_encode($layTpSys);
        $update = $this->model
            ->where('group', '=', 'layTpSys')
            ->where('key', '=', 'configGroup')
            ->update(['value' => $value]);
        if ($update !== false) {
            return $this->updateConfig();
        } else {
            return $this->error('操作失败');
        }
    }

    //删除分组
    public function delGroup(){
        $group = $this->request->param('group');
        $config = $this->model->where([
            ['group','=','layTpSys'],
            ['key','=','configGroup']
        ])->value('value');
        $layTpSys = json_decode($config, JSON_UNESCAPED_UNICODE);
        if(in_array($group,['basic','upload'])){
            return $this->error("不允许删除此分组");
        }
        if(array_key_exists($group,$layTpSys)){
            unset($layTpSys[$group]);
        }
        Db::startTrans();
        try{
            $value = json_encode($layTpSys);
            $result[] = $this->model
                ->where('group','=','layTpSys')
                ->where('key','=','configGroup')
                ->update(['value'=>$value]);
            $res = $this->model->where([
                ['group','=',$group]
            ])->delete();
            $result[] = $res === false ? false : true;
            if(check_res($result)){
                DB::commit();
                return $this->updateConfig();
            }else{
                DB::rollback();
                return $this->error('操作失败');
            }
        }catch (\Exception $e){
            return $this->error($e->getMessage());
        }
    }

    /**
     * 添加配置
     * @return array|\think\response\Json
     */
    public function addConfig(){
        $post = filter_post_data($this->request->post());
        //检测group,key是否存在
        $keyExist = $this->model->where([
            ['group','=',$post['group']],
            ['key','=',$post['key']]
        ])->find();
        if($keyExist){
            return $this->error('变量名'.$post['key'].'已存在');
        }
        $keyExist = $this->model->where([
            ['group','=',$post['group']],
            ['name','=',$post['name']]
        ])->find();
        if($keyExist){
            return $this->error('配置名称'.$post['name'].'已存在');
        }
        //处理content字段值
        if(in_array($post['type'],['select_single','select_multi','checkbox','switch'])){
            $content = explode("\n", $post['content']);
            $return = [];
            foreach($content as $v){
                $temp = explode('=', $v);
                $return[] = ['value'=>$temp[0],'text'=>$temp[1]];
            }
            $post['content'] = json_encode($return, JSON_UNESCAPED_UNICODE);
        }else{
            unset($post['content']);
        }
        $update_res = $this->model->insert($post);
        if( $update_res ){
            return $this->updateConfig();
        }else if( $update_res === null ){
            return $this->error('操作失败');
        }
    }

    //删除
    public function del(){
        $ids = array_filter(explode(',',$this->request->param('ids')));
        if(!$ids){
            return $this->error('参数ids不能为空');
        }
        $config = $this->model->find($ids);
        if(!$config){
            return $this->error('参数ids不存在');
        }
        if(in_array($config->group.'.'.$config->key,[
            'basic.siteName','basic.loginCaptcha','basic.firstMenuNum',
            'upload.domain','upload.maxsize','upload.mimeType','upload.switch'
        ])){
            return $this->error('不允许删除此配置');
        }

        if( $this->model->destroy($ids) ){
            return $this->updateConfig();
        }else{
            return $this->error('数据删除失败');
        }
    }

    //设置配置
    public function set(){
        $post = $this->request->post();
        $group = $post['group'];
        unset($post['group']);
        foreach($post as $k=>$v){
            if(is_array($v)){
                $temp = [];
                foreach($v['key'] as $arr_k=>$arr_v){
                    $temp[$arr_v] = $v['value'][$arr_k];
                }
                $post[$k] = json_encode($temp);
            }
        }
        $result = [];
        Db::startTrans();
        foreach($post as $k=>$v){
            $update = $this->model
                ->where('group','=',$group)
                ->where('key','=',$k)
                ->update(['value'=>$v]);
            if($update !== false){
                $result[] = true;
            }
        }
        if(check_res($result)){
            Db::commit();
            return $this->updateConfig();
        }else{
            Db::rollback();
            return $this->error('操作失败');
        }
    }

    /**
     * 更新配置文件
     * @return array|\think\response\Json
     */
    public function updateConfig()
    {
        try {
            //写入配置文件
            $fileName = root_path() . DS . 'config' . DS . 'laytp.php';
            $config = $this->model->field('group,key,value,type')->select()->toArray();
            $resultConfig = [];
            foreach ($config as $k => $v) {
                if ($v['type'] == 'array') {
                    $resultConfig[$v['group']][$v['key']] = json_decode($v['value'], true);
                } else {
                    $resultConfig[$v['group']][$v['key']] = $v['value'];
                }
            }
            file_put_contents($fileName, "<?php\nreturn " . var_export($resultConfig, true) . ';');
            return $this->success('缓存更新成功');
        } catch (\Exception $e) {
            return $this->error('缓存更新失败:' . $e->getMessage());
        }
    }
}