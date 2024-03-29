<?php

namespace laytp\library;
use think\facade\Cache;

/**
 * Redis锁
 */
class Redis
{
    /**
     * 得到一个redis锁
     * @param $name string 锁名称
     * @param $ttl int 锁存在的时间，单位秒，默认60秒
     * @return bool
     */
    public static function getLock($name, $ttl=60)
    {
        set_time_limit(0);
        $redis = Cache::store('redis')->handler();
        while(true){
            if($redis->setnx($name, 1)){
                $redis->expire($name, $ttl);
                break;
            }
        }
        return true;
    }

    /**
     * 删除一个redis锁
     * @param $name string 锁名称
     * @return bool
     */
    public static function delLock($name)
    {
        $redis = Cache::store('redis')->handler();
        $redis->del($name);
        return true;
    }
}