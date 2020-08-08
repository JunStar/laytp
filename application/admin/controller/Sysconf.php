<?php
namespace app\admin\controller;

use controller\Backend;
use think\Db;
use think\Exception;
use think\facade\Config;
use think\facade\Env;

class Sysconf extends Backend
{
    protected $model;
    public $has_del=1;//是否拥有删除功能
    public $has_soft_del=0;//是否拥有软删除功能

    public function initialize()
    {
        parent::initialize();
        $this->model = new \app\admin\model\Sysconf();
    }

    public function index(){
        $dictionary = json_decode( model('Sysconf')->where('group','=','dictionary')->value('value'), true );
        $dictionary = $dictionary ? $dictionary : [];
        if(array_key_exists('dictionary', $dictionary)){
            unset($dictionary['dictionary']);
        }
        $config_group = $dictionary;
        $this->assign('config_group', $config_group);

        $group = $this->request->param('group');
        $group = $group ? $group : key($config_group);
        $this->assign('group', $group);

        $config = model('Sysconf')->where('group','=',$group)->order('id','asc')->select()->toArray();
        foreach($config as $k=>$v){
            if($v['type'] == 'array'){
                $config[$k]['value'] = json_decode( $v['value'], true );
            }
            $temp = json_decode( $v['content'], true );
            if(is_array($temp)){
                $config[$k]['content'] = $temp;
                $config[$k]['content_value'] = array_keys($temp);
                $config[$k]['content_text'] = array_values($temp);
            }
        }
        $this->assign('config', $config);

        return $this->fetch();
    }

    //添加分组
    public function add_group(){
        if( $this->request->isAjax() && $this->request->isPost() ){
            $post = filterPostData($this->request->post("row/a"));
            $config = Db::table(Config::get('database.prefix').'sysconf')->where([
                ['group','=','dictionary'],
                ['key','=','config']
            ])->value('value');
            $config_arr = json_decode($config, JSON_UNESCAPED_UNICODE);
            $config_arr[$post['group']] = $post['group_name'];
            $value = json_encode($config_arr);
            $update = Db::table(Config::get('database.prefix').'sysconf')
                ->where('group','=','dictionary')
                ->where('key','=','config')
                ->update(['value'=>$value]);
            if($update !== false){
                $update_config = $this->update_config();
                if( $update_config['code'] == 1 ){
                    $this->success('操作成功',['up_reload'=>true]);
                }else{
                    $this->error($update_config['msg']);
                }

            }else{
                $this->error('操作失败');
            }
        }
        return $this->fetch();
    }

    //删除分组
    public function del_group(){
        $group = $this->request->param('group');
        $config = Db::table(Config::get('database.prefix').'sysconf')->where([
            ['group','=','dictionary'],
            ['key','=','config']
        ])->value('value');
        $config_arr = json_decode($config, JSON_UNESCAPED_UNICODE);
        if(array_key_exists($group,$config_arr)){
            unset($config_arr[$group]);
        }
        $value = json_encode($config_arr);
        $update = Db::table(Config::get('database.prefix').'sysconf')
            ->where('group','=','dictionary')
            ->where('key','=','config')
            ->update(['value'=>$value]);
        Db::table(Config::get('database.prefix').'sysconf')->where([
            ['group','=',$group]
        ])->delete();
        if($update !== false){
            $update_config = $this->update_config();
            if( $update_config['code'] == 1 ){
                $this->success('操作成功');
            }else{
                $this->error($update_config['msg']);
            }
        }else{
            $this->error('操作失败');
        }
    }

    //添加配置属性
    public function add_config(){
        if( $this->request->isAjax() && $this->request->isPost() ){
            $post = filterPostData($this->request->post("row/a"));
            //检测group,key是否存在
            $exist = model('Sysconf')->where(['group'=>$post['group'],'key'=>$post['key']])->find();
            if($exist){
                return $this->error($post['key'].'已存在');
            }
            $content = explode("\n", $post['content']);
            $return = [];
            foreach($content as $v){
                $temp = explode('|', $v);
                $return[$temp[0]] = $temp[1];
            }
            $post['content'] = json_encode($return, JSON_UNESCAPED_UNICODE);
            $update_res = model('Sysconf')->insert($post);
            if( $update_res ){
                //写入配置文件
                $update_config = $this->update_config();
                if( $update_config['code'] == 1 ){
                    $this->success('操作成功',['up_reload'=>true]);
                }else{
                    $this->error($update_config['msg']);
                }
                $this->success('操作成功');
            }else if( $update_res === null ){
                $this->error('操作失败');
            }
        }
        $dictionary = json_decode( model('Sysconf')->where('group','=','dictionary')->value('value'), true );
        $dictionary = $dictionary ? $dictionary : [];
        if(array_key_exists('dictionary', $dictionary)){
            unset($dictionary['dictionary']);
        }
        $config_group = $dictionary ? $dictionary : ['basic' => '基础配置', 'upload' => '上传配置'];
        $this->assign('config_group', $config_group);
        return $this->fetch();
    }

    //添加配置属性
    public function add(){
        if( $this->request->isAjax() && $this->request->isPost() ){
            $post = filterPostData($this->request->post("row/a"));
            //检测group,key是否存在
            $exist = model('Sysconf')->where(['group'=>$post['group'],'key'=>$post['key']])->find();
            if($exist){
                return $this->error($post['key'].'已存在');
            }
            $content = explode("\n", $post['content']);
            $return = [];
            foreach($content as $v){
                $temp = explode('|', $v);
                $return[$temp[0]] = $temp[1];
            }
            $post['content'] = json_encode($return, JSON_UNESCAPED_UNICODE);
            $update_res = model('Sysconf')->insert($post);
            if( $update_res ){
                //写入配置文件
                $update_config = $this->update_config();
                if( $update_config['code'] == 1 ){
                    $this->success('操作成功');
                }else{
                    $this->error($update_config['msg']);
                }
                $this->success('操作成功');
            }else if( $update_res === null ){
                $this->error('操作失败');
            }
        }
        return $this->fetch();
    }

    //设置配置
    public function set_config(){
        if( $this->request->isAjax() && $this->request->isPost() ){
            $post = $this->request->post("row/a");
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
                $update = Db::table(Config::get('database.prefix').'sysconf')
                    ->where('group','=',$group)
                    ->where('key','=',$k)
                    ->update(['value'=>$v]);
                if($update !== false){
                    $result[] = true;
                }
            }
            if(check_res($result)){
                Db::commit();
                $update_config = $this->update_config();
                if( $update_config['code'] == 1 ){
                    $this->success('操作成功');
                }else{
                    $this->error($update_config['msg']);
                }
            }else{
                Db::rollback();
                $this->error('操作失败');
            }
        }
    }

    //删除
    public function del(){
        $ids = $this->request->param('ids');
        if( $this->model->where('id','in',$ids)->delete() ){
            $this->update_config();
            $this->success('操作成功');
        }else{
            $this->error('操作失败1');
        }
    }

    /**
     * 更新配置文件
     * @return array
     */
    public function update_config(){
        try{
            //写入配置文件
            $file_name = Env::get('root_path') .  DS . 'config' . DS . 'laytp.php';
            $config = model('Sysconf')->field('group,key,value,type')->select()->toArray();
            $result_config = [];
            foreach($config as $k=>$v){
                if($v['type'] == 'array'){
                    $result_config[$v['group']][$v['key']] = json_decode( $v['value'], true );
                }else{
                    $result_config[$v['group']][$v['key']] = $v['value'];
                }
            }
            file_put_contents($file_name,"<?php\nreturn ".var_export($result_config,true).';');
            return ['code'=>1];
        }catch (Exception $e){
            return ['code'=>0,'msg'=>$e->getMessage()];
        }
    }
}