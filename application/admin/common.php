<?php
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