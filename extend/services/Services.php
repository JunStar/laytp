<?php
/**
 * Service基类
 */

namespace services;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use think\Exception;
use think\facade\Env;

class Services
{
    public static function success($msg = '', $data = null){
        $result = [
            'code' => 1,
            'msg'  => $msg,
            'data' => $data,
        ];
        return $result;
    }

    public static function error($msg = ''){
        $result = [
            'code' => 0,
            'msg'  => $msg
        ];
        return $result;
    }

    /**
     * 是否有冲突
     *
     * @param   string $name 插件名称
     * @return  boolean
     * @throws  Exception
     */
    public static function noConflict($name)
    {
        // 检测冲突文件
        $list = self::getGlobalFiles($name, true);
        if ($list) {
            //发现冲突文件，抛出异常
            throw new \AddonException("发现冲突文件", -3, ['conflictlist' => $list]);
        }
        return true;
    }

    /**
     * 获取插件在全局的文件
     *
     * @param   string $name 插件名称
     * @return  array
     */
    public static function getGlobalFiles($name, $onlyconflict = false)
    {
        $list = [];
        $addonDir = Env::get('root_path') . 'addons' . DS . $name . DS;
        // 扫描插件目录是否有覆盖的文件
        foreach (self::getCheckDirs() as $k => $dir) {
            $checkDir = Env::get('root_path') . $dir . DS;
            if (!is_dir($checkDir))
                continue;
            //检测到存在插件外目录
            if (is_dir($addonDir . $dir)) {
                //匹配出所有的文件
                $files = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($addonDir . $dir, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST
                );

                foreach ($files as $fileinfo) {
                    if ($fileinfo->isFile()) {
                        $filePath = $fileinfo->getPathName();
                        $path = str_replace($addonDir, '', $filePath);
                        if ($onlyconflict) {
                            $destPath = Env::get('root_path') . $path;
                            if (is_file($destPath)) {
                                if (filesize($filePath) != filesize($destPath) || md5_file($filePath) != md5_file($destPath)) {
                                    $list[] = $path;
                                }
                            }
                        } else {
                            $list[] = $path;
                        }
                    }
                }
            }
        }
        return $list;
    }

    /**
     * 获取检测的全局文件夹目录
     * @return  array
     */
    protected static function getCheckDirs()
    {
        return [
            'application',
            'public'
        ];
    }
}