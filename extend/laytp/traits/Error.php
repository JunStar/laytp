<?php
/**
 * 设置错误和获取错误公用方法定义，任何类都可以使用这两个方法
 */

namespace laytp\traits;

trait Error
{
    protected $_error = null;

    public function getError()
    {
        return $this->_error;
    }

    public function setError($error)
    {
        $this->_error = $error;
    }
}