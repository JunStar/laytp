<?php
/**
 * Service基类
 */
namespace service;

class Service
{
    protected $_error = null;

    public function getError(){
        return $this->_error;
    }

    public function setError($error){
        $this->_error = $error;
    }
}