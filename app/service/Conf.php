<?php

namespace app\service;

use laytp\library\UploadDomain;
use laytp\traits\Error;
use think\facade\Cache;
use think\facade\Config;

/**
 * 系统配置服务器实现者
 * Class Auth
 * @package app\service
 */
class Conf
{
    use Error;

    protected $redis = null;

    protected $db = null;

    //判断是否配置了redis
    protected function hasRedis(){
        $redisConf = Config::get('cache');
        if(isset($redisConf['stores']['redis']['type'])){
            return true;
        }
        return false;
    }

    /**
     * 通过一个完整的key，获取配置信息
     * @param $wholeKey
     * @param $defaultValue
     * @return bool|mixed|string
     */
    public function get($wholeKey, $defaultValue='')
    {
        if($this->hasRedis()){
            $redis = Cache::store('redis')->handler();
            if($redis){
                $value = $redis->hget($wholeKey, 'value');
                $formType = $redis->hget($wholeKey, 'form_type');
                if($formType == 'array') return json_decode($value, JSON_UNESCAPED_UNICODE);
                return $value ? $value : $defaultValue;
            }
        }
        list($group, $key) = $this->getGroupKey($wholeKey);
        $conf = \app\model\Conf::where(['group'=>$group, 'key'=>$key])->findOrEmpty()->toArray();
        if($conf){
            $value = $conf['value'];
            $formType = $conf['form_type'];
            if($formType == 'array') return json_decode($value, JSON_UNESCAPED_UNICODE);
            if($formType == 'upload'){
                $fileInfo = UploadDomain::multiJoin($value);
                $return[$key] = $value;
                if($fileInfo){
                    $return[$key.'_path'] = $fileInfo['path'];
                    $return[$key.'_filename'] = $fileInfo['filename'];
                }else{
                    $return[$key.'_path'] = '';
                    $return[$key.'_filename'] = '';
                }
                return $return;
            }
            return $value ? $value : $defaultValue;
        }else{
            return '';
        }
    }

    /**
     * 通过一个完整的key，设置配置信息
     * @param $wholeKey
     * @param $value
     * @return bool
     */
    public function set($wholeKey, $value)
    {
        if(is_array($value)){
            $value = json_encode($value, JSON_UNESCAPED_UNICODE);
        }
        list($group, $key) = $this->getGroupKey($wholeKey);
        $id = \app\model\Conf::where(['group'=>$group, 'key'=>$key])->value('id');
        if($id){
            \app\model\Conf::where('id', '=', $id)->save(['group' => $group, 'key' => $key]);
        }else{
            \app\model\Conf::insert(['group' => $group, 'key' => $key, 'value' => $value]);
        }

        if($this->hasRedis()){
            $redis = Cache::store('redis')->handler();
            $redis->set($wholeKey, $value);
        }

        return true;
    }

    public function del($group, $key)
    {
        \app\model\Conf::where(['group' => $group, 'key' => $key])->delete();

        if($this->hasRedis()){
            $redis = Cache::store('redis')->handler();
            $redis->del($group . $key);
        }

        return ;
    }

    /**
     * 通过数组，设置配置信息
     * @param $array
     * @return bool
     */
    public function groupSet($array)
    {
        foreach($array as $item){
            $item['value'] = is_array($item['value']) ? json_encode($item['value'], JSON_UNESCAPED_UNICODE) : $item['value'];
            $id = \app\model\Conf::where(['group'=>$item['group'], 'key'=>$item['key']])->value('id');
            if($id){
                \app\model\Conf::where('id', '=', $id)->save($item);
            }else{
                \app\model\Conf::create($item);
            }

            if($this->hasRedis()){
                $redis = Cache::store('redis')->handler();
                $hashKey = $item['group'] . '.' . $item['key'];
                $redis->hset($hashKey, 'group', $item['group']);
                $redis->hset($hashKey, 'key', $item['key']);
                $redis->hset($hashKey, 'value', $item['value']);
                $redis->hset($hashKey, 'form_type', $item['form_type']);
            }
        }
        return true;
    }

    /**
     * 通过配置分组名称，获取整个分组的信息
     * @param $group
     * @param $onlyMysql boolean 是否仅从数据库取配置
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function groupGet($group, $onlyMysql=false)
    {
        $return = [];
        $items = [];

        if(!$onlyMysql){
            if($this->hasRedis()){
                $redis = Cache::store('redis')->handler();
                $keys = $redis->keys($group.'*');
                foreach($keys as $key){
                    $items[$key] = $redis->hGetAll($key);
                }
                if(!$items){
                    $items = \app\model\Conf::where(['group'=>$group])->select()->toArray();
                }
            }else{
                $items = \app\model\Conf::where(['group'=>$group])->select()->toArray();
            }
        }else{
            $items = \app\model\Conf::where(['group'=>$group])->select()->toArray();
        }

        foreach ($items as $k => $v) {
            if ($v['form_type'] === 'array') {
                $array = json_decode($v['value'], true);
                if(!$array){
                    $return[$v['key']] = [""=>""];
                }else{
                    $return[$v['key']] =$array;
                }
            } elseif($v['form_type'] === 'upload') {
                $fileInfo = UploadDomain::multiJoin($v['value']);
                if($fileInfo){
                    $return[$v['key']] = $fileInfo['id'];
                    $return[$v['key'].'_path'] = $fileInfo['path'];
                    $return[$v['key'].'_filename'] = $fileInfo['filename'];
                }else{
                    $return[$v['key']] = '';
                    $return[$v['key'].'_path'] = '';
                    $return[$v['key'].'_filename'] = '';
                }
            } else {
                $return[$v['key']] = $v['value'];
            }
        }
        return $return;
    }

    /**
     * 通过完整的key，获取到分组名称和key值
     * @param $wholeKey
     * @return array|boolean
     */
    protected function getGroupKey($wholeKey)
    {
        $arr = explode('.', $wholeKey);
        if(!$arr || count($arr) == 1){
            $this->setError('请输入一个完整的key，一个完整的key必须包含至少一个.号');
            return false;
        }
        $key = $arr[count($arr) - 1];
        $group = substr($wholeKey, 0 , strrpos($wholeKey, '.'));
        return [$group, $key];
    }
}