<?php

namespace laytp\library;

/**
 * 通用树型类 - laytp极速后台框架
 * @Author:  JunStar
 * @Version: 1.0.0
 * @Date:    2022-1-7 21:48:38
 * @LastModified by:   JunStar
 * @LastModified time: 2022-1-8 21:45:22
 */
class Tree
{
    protected static $instance;

    /**
     * 生成树型结构所需要的2维数组
     * @var array
     */
    public $map = [];
    public $mapName = 'id';
    public $pidName = 'pid';
    public $childName = 'children';

    /**
     * 初始化
     * @access public
     * @return Tree
     */
    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    /**
     * 初始化方法
     *  注意，参数一定要是数组，数据库select()->toArray()；否则调用getRootTree会内存溢出
     * @param array 2维数组，例如：
     * array(
     *      1 => array('id'=>'1','pid'=>0,'name'=>'一级栏目一'),
     *      2 => array('id'=>'2','pid'=>0,'name'=>'一级栏目二'),
     *      3 => array('id'=>'3','pid'=>1,'name'=>'二级栏目一'),
     *      4 => array('id'=>'4','pid'=>1,'name'=>'二级栏目二'),
     *      5 => array('id'=>'5','pid'=>2,'name'=>'二级栏目三'),
     *      6 => array('id'=>'6','pid'=>3,'name'=>'三级栏目一'),
     *      7 => array('id'=>'7','pid'=>3,'name'=>'三级栏目二')
     *      )
     * @return $this
     */
    public function init($arr = [])
    {
        $map = [];
        //生成以id为key的map
        foreach ($arr as &$it){
            $map[$it['id']] = &$it;
        }
        $this->map = $map;
        return $this;
    }

    /**
     * 获取所有根树
     * @return array
     */
    public function getRootTrees()
    {
        $map = $this->map;
        $res = [];
        $rootTree = [];
        foreach ($map as $id => &$item) {
            // 获取出每一条数据的父id
            $pid = &$item[$this->pidName];
            // 如果在map中没有设置过当前$item的pid索引, 说明$item是根节点
            if(!isset($map[$pid])){
                //将根节点的item的引用保存到$res中
                $res[] = &$item;
            }else{
                // 如果在map中有设置过当前$item的pid索引, 则将当前item加入到他父亲的叶子节点中
                // 此处关键需要理解内存地址引用，修改了$pItem，其实就是修改了$map[$pid]的值
                $pItem = &$map[$pid];
                $pItem[$this->childName][] = &$item;
            }
        }

        // 循环res，将pid不为0的过滤掉，剩下的就是所有的根树
        if($res){
            foreach($res as $k=>$value){
                if($value[$this->pidName] == 0){
                    $rootTree[] = $value;
                }
            }
        }

        return $rootTree;
    }

    /**
     * 获取所有树
     */
    public function getTrees(){
        $map = $this->map;
        $res = [];
        foreach ($map as $id => &$item) {
            // 获取出每一条数据的父id
            $pid = &$item[$this->pidName];
            // 如果在map中没有设置过当前$item的pid索引, 说明$item是根节点
            if(!isset($map[$pid])){
                //将根节点的item的引用保存到$res中
                $res[] = &$item;
            }else{
                // 如果在map中有设置过当前$item的pid索引, 则将当前item加入到他父亲的叶子节点中
                // 此处关键需要理解内存地址引用，修改了$pItem，其实就是修改了$map[$pid]的值
                $pItem = &$map[$pid];
                $pItem[$this->childName][] = &$item;
            }
        }

        return $res;
    }

    /**
     * 获取某棵子树
     *  思路:
     *      1.先获取当前节点的所有父级节点
     *      2.组合一个根树的map，每个key都是id值，getRootTree是自增的
     *      3.使用所有父级节点，直接在根树的map中索引得到子树
     */

    /**
     * 获取某ID的所有子级ID
     * @param $ids string|array 要查询子级的ID
     * @param bool $withSelf 返回的结果是否包含$ids
     * @return array
     */
    public function getChildIds($ids, $withSelf=true){
        $map = $this->map;
        $res = [];//最终结果
        if(!is_array($ids)){
            $ids = explode(',', $ids);
            $sourceIds = $ids;
        }else{
            $sourceIds = $ids;
        }

        $state = true;
        while($state){
            $otherIds = [];
            foreach ($ids as $id) {
                foreach ($map as $key => $value) {
                    if($value[$this->pidName] == $id){
                        $res[] = $value[$this->mapName];//找到我的下级立即添加到最终结果中
                        $otherIds[] = $value[$this->mapName];//将我的下级id保存起来用来下轮循环他的下级
                    }
                }
            }

            if(!$otherIds){
                $state = false;
            }else{
                $ids = $otherIds;//foreach中找到的我的下级集合,用来下次循环
            }
        }

        if($withSelf){
            foreach($sourceIds as $sId){
                $res[] = intval($sId);
            }
        }

        return $res;
    }

    /**
     * 获取某ID的所有父级ID
     * @param $ids string|array 要查询父级的ID
     * @param bool $withSelf 返回的结果是否包含$id
     * @return array
     */
    public function getParentIds($ids, $withSelf=true){
        $map = $this->map;
        $res = [];//最终结果
        if(!is_array($ids)){
            $ids = explode(',', $ids);
            $sourceIds = $ids;
        }else{
            $sourceIds = $ids;
        }

        $state = true;
        while($state){
            $otherIds = [];
            foreach ($ids as $id) {
                foreach ($map as $key => $value) {
                    if($value[$this->mapName] == $map[$id][$this->pidName]){
                        $res[] = $value[$this->mapName];//找到我的上级立即添加到最终结果中
                        $otherIds[] = $value[$this->mapName];//将我的上级id保存起来用来下次轮循环他的下级
                    }
                }
            }

            if(!$otherIds){
                $state = false;
            }else{
                $ids = $otherIds;//foreach中找到的我的上级集合,用来下次循环
            }
        }

        if($withSelf){
            foreach($sourceIds as $sId){
                $res[] = intval($sId);
            }
        }

        return $res;
    }
}