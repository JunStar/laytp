<?php
/**
 * 后台系统配置模型
 */

namespace plugin\core\model\autocreate\curd;

use laytp\BaseModel;

class Field extends BaseModel
{
    protected $name = 'plugin_autocreate_curd_field';

    protected function getAdditionAttr($addition)
    {
        return json_decode($addition, true);
    }
}