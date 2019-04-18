<?php

namespace Cmfx\Logger;

/**
 * 日志记录器接口
 */
interface WriterInterface
{
    /**
     * 存储日志数据
     * @param array $data
     */
    public function write($data);
}
