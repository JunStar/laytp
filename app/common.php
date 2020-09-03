<?php
/**
 * 公用函数库
 */

/**
 * 统一处理post数据
 * @param $post
 * @return mixed
 */
function filter_post_data($post){
    if(!$post){
        return [];
    }
    //处理数组
    foreach($post as $k=>$v){
        if(is_array($v)){
            $post[$k] = implode(',',$v);
        }
    }
    return $post;
}

/**
 * 检测数组中的值是否都为true，启用数据库事务时能用到
 *      注意，更新操作返回影响数据库行数，当没有对数据进行更改时，正常操作数据库会返回0，表示影响了0行，
 *      由于0在PHP中表示false，但是在实际业务中，有可能影响0行也允许提交事务，
 *      此时需要根据业务需求，在controller层调用checkRes函数时，对入参$result进行判断，返回为0时设置为false还是true，
 *      以便用于判断最终执行回滚还是提交事务的操作
 * @param $result
 * @return bool
 */
function check_res($result){
    foreach($result as $v){
        if(!$v){
            return false;
        }
    }
    return true;
}
