<?php

namespace laytp\library;

/**
 * 字符串处理
 */
class Str
{
    /**
     * 下划线转驼峰
     * @param $str
     * @return string|string[]|null
     */
    public static function underlineToCamel($str)
    {
        $str = preg_replace_callback('/([-_]+([a-z]{1}))/i', function ($matches) {
            return strtoupper($matches[2]);
        }, $str);
        return $str;
    }

    // 生成密码
    public static function createPassword($password)
    {
        return password_hash(md5($password), PASSWORD_DEFAULT);
    }

    // 检验密码
    public static function checkPassword($password, $passwordHash)
    {
        return password_verify(md5($password), $passwordHash);
    }
}