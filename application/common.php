<?php
function layui_table_data($data){
    $json['code'] = ($data['total'] > 0) ? 0 : 1;
    $json['msg'] = '暂无数据';
    $json['count'] = $data['total'];
    $json['data'] = $data['data'];
    return json($json);
}