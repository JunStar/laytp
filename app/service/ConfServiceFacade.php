<?php

namespace app\service;

use think\Facade;

/**
 * 系统配置服务门面
 * @package app\service
 * @method static mixed get($wholeKey, $defaultValue='') 通过完整的key获取配置信息
 * @method static mixed set($wholeKey, $value) 通过完整的key设置一个配置信息
 * @method static mixed del($group, $key) 删除某个分组下某个key的配置
 * @method static mixed groupGet($group, $onlyMysql=false) 通过分组名称，获取整个分组的配置信息
 * @method static mixed groupSet($array) 通过hash数组，设置整个分组的配置信息
 */
class ConfServiceFacade extends Facade
{
    protected static function getFacadeClass()
    {
        return Conf::class;
    }
}
