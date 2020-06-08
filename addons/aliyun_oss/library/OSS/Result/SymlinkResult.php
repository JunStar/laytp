<?php

namespace addons\aliyun_oss\library\OSS\Result;

use addons\aliyun_oss\library\OSS\Core\OssException;
use addons\aliyun_oss\library\OSS\OssClient;

/**
 *
 * @package OSS\Result
 */
class SymlinkResult extends Result
{
    /**
     * @return string
     * @throws OssException
     */
    protected function parseDataFromResponse()
    {
        $this->rawResponse->header[OssClient::OSS_SYMLINK_TARGET] = rawurldecode($this->rawResponse->header[OssClient::OSS_SYMLINK_TARGET]);
        return $this->rawResponse->header;
    }
}

