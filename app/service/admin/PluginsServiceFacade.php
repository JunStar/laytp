<?php

namespace app\service\admin;

use think\Facade;

/**
 * 插件市场服务门面
 * @package app\service\admin
 * @method static mixed getError() 获取错误信息
 * @method static mixed offLineInstall() 离线安装
 * @method static mixed unInstall($plugin) 卸载
 * @method static mixed getPluginPath($plugin) 获取插件目录
 * @method static mixed install($plugin, $laytpGwToken) 安装插件
 */
class PluginsServiceFacade extends Facade
{
    protected static function getFacadeClass()
    {
        return Plugins::class;
    }
}
