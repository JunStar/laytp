<?php

namespace library;

use DateTime;
use DateTimeZone;

/**
 * 文件夹和文件处理类
 */
class DirFile{
    /**
     * 创建目录
     * @param $path
     * @param int $mode
     * @return bool
     */
    public static function createDir($path,$mode = 0777){
        if(is_dir($path)){
            return true;
        }else{
            //如果目录不存在，则创建
            if(mkdir($path,$mode,true)){
                return true;
            }else{
                return false;
            }
        }
    }

    /**
     * 循环遍历目录下的文件和文件夹并输出
     * @param $pathName
     * @param array $filterDir
     * @param array $filterFile
     * @param array $selected
     * @param array $output
     * @return array|null
     */
    public static function recurDir($pathName, $filterDir=[], $filterFile=[], $selected=[],$output=['fullName'=>'fullName','baseName'=>'baseName']){
        //将结果保存在result变量中
        $result = array();
        $temp = array();
        //判断传入的变量是否是目录
        if(!is_dir($pathName) || !is_readable($pathName)) {
            return null;
        }
        //取出目录中的文件和子目录名,使用scandir函数
        $allFiles = scandir($pathName);
        //遍历他们
        foreach($allFiles as $fileName) {
            if(in_array($fileName, array('.', '..'))) {
                continue;
            }
            //路径加文件名
            if(mb_substr($pathName,-1,1) == DS){
                $fullName = $pathName.$fileName;
            }else{
                $fullName = $pathName.DS.$fileName;
            }
            $baseName = basename( $fileName );
            //如果是目录的话就继续遍历这个目录
            if(is_dir($fullName)) {
                if(in_array($fullName,$filterDir)){
                    continue;
                }
                //将这个目录中的文件信息存入到数组中
                $res = [$output['baseName']=>$baseName,$output['fullName']=>$fullName,'type'=>'dir','children'=>self::recurDir($fullName,$filterDir,$filterFile,$selected,$output)];
                if(in_array($fullName,$selected)){
                    $res['state'] = ['selected'=>true];
                }else{
                    $res['state'] = ['selected'=>false];
                }
                $result[] = $res;
            }else {
                if(in_array($fullName,$filterFile)){
                    continue;
                }
                //如果是文件就先存入临时变量
                //将这个目录中的文件信息存入到数组中
                $tem = [$output['baseName']=>$baseName,$output['fullName']=>$fullName,'type'=>'file','state'=>['selected'=>false]];
                if(in_array($fullName,$selected)){
                    $tem['state'] = ['selected'=>true];
                }else{
                    $tem['state'] = ['selected'=>false];
                }
                $temp[] = $tem;
            }
        }
        //取出文件
        if($temp) {
            foreach($temp as $f) {
                $result[] = $f;
            }
        }
        return $result;
    }

    /**
     * 删除目录
     * @param $dir
     * @return bool
     */
    public static function delDir($dir){
        //先删除目录下的文件：
        $dh=opendir($dir);
        while ($file=readdir($dh)) {
            if($file!="." && $file!="..") {
                $fullpath=$dir."/".$file;
                if(!is_dir($fullpath)) {
                    unlink($fullpath);
                } else {
                    delDir($fullpath);
                }
            }
        }

        closedir($dh);
        //删除当前文件夹：
        if(rmdir($dir)) {
            return true;
        } else {
            return false;
        }
    }
}