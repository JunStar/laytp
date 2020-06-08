<?php

namespace addons\aliyun_oss\library\OSS\Result;


/**
 * Class BodyResult
 * @package OSS\Result
 */
class BodyResult extends Result
{
    /**
     * @return string
     */
    protected function parseDataFromResponse()
    {
        return empty($this->rawResponse->body) ? "" : $this->rawResponse->body;
    }
}