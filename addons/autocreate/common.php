<?php
function getSelectMenuIds($menu_tree_obj, $id, $init=false){
    static $select_menu_ids;
    if($init){
        $select_menu_ids = [];
    }
    $tree = $menu_tree_obj->getTreeArray($id);
    if($tree){
        $select_menu_ids[] = $tree[0]['id'];
        if(count($tree[0]['children'])){
            getSelectMenuIds($menu_tree_obj,$tree[0]['id']);
        }
        return $select_menu_ids;
    }else{
        return [];
    }
}

/**
 * 分页数据格式化成layui_table能用的数据
 * @param $data
 * @return \think\response\Json
 */
function layui_table_page_data($data){
    if( array_key_exists('total', $data) ){
        $json['code'] = ($data['total'] > 0) ? 0 : 1;
    }else{
        $data['total'] = 0;
        $json['code'] = 1;
    }
    $json['msg'] = '暂无数据';
    $json['count'] = $data['total'];

    if( array_key_exists('data', $data) ){
        $json['data'] = $data['data'];
    }else{
        $json['data'] = [];
    }
    return json($json);
}