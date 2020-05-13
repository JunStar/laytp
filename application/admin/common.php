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
 * @param $menus
 * @param $id
 * @return array
 */
function get_crumbs($menus,$id){
    static $select_menu,$crumbs;
    foreach($menus as $k=>$v){
        if($v['id'] == $id){
            $select_menu[] = $v;
            $crumbs[] = $v['name'];
            $crumbs[] = '>';
            if($v['pid'] > 0){
                get_crumbs($menus,$v['pid']);
            }
            break;
        }
    }
    return [$select_menu, array_reverse($crumbs)];
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

/**
 * 获取不分页的layui_table数据
 * @param $data
 * @return \think\response\Json
 */
function layui_table_data($data){
    $count_data = count($data);
    $json['code'] = ($count_data > 0) ? 0 : 1;
    $json['msg'] = '暂无数据';
    $json['data'] = $data;
    return json($json);
}

function select_page_data($data){
    $return['list'] = $data['data'];
    $return['totalRow'] = $data['total'];
    return $return;
}

/**
 * 获取某个字段的静态常量值列表
 * @param $field_name
 * @param $field_val
 * @param $const
 * @return string
 */
function get_const_val($field_name, $field_val, $const){
    $result = [];
    $field_val_arr = explode(',', $field_val);
    foreach($const[$field_name] as $k=>$v){
        foreach($field_val_arr as $fv){
            if($fv == $k){
                $result[] = $v;
            }
        }
    }
    return implode(',', $result);
}

/**
 * 生成普通多选下拉框的Js常量
 * @param $array
 * @return false|string
 */
function getSelectMultiJsConst($array){
    $result = [];
    foreach($array as $v=>$name){
        $result[] = ['id'=>$v,'name'=>$name];
    }
    return json_encode($result);
}

/**
 * 生成复选框的Js常量
 * @param $array
 * @return false|string
 */
function getCheckboxJsConst($array){
    return json_encode($array);
}

/**
 * 统一处理post数据
 * @param $post
 * @return mixed
 */
function filterPostData($post){
    if(!$post){
        return [];
    }
    foreach($post as $k=>$v){
        if(is_array($v)){
            $post[$k] = implode(',',$v);
        }
    }
    return $post;
}

/**
 * 获取默认头像
 * @param $avatar
 * @return string
 */
function getDefaultAvatar($avatar){
    if(!$avatar){
        return '/static/admin/image/default_avatar.jpg';
    }else{
        return $avatar;
    }
}