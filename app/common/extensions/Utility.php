<?php
namespace UI\Extensions;

/**
 * 工具类
 */
class Utility
{
    /**
     * 获取客户端IP
     */
    public static function clientIp()
    {
        $items = [
            'HTTP_CDN_SRC_IP',
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'REMOTE_ADDR',
        ];

        $ip = '';
        foreach ($items as $item) {
            if (!empty($_SERVER[$item])) {
                $ip = $_SERVER[$item];
                break;
            }
        }

        return preg_match("/^\d{1,3}(\.\d{1,3}){3}$/", $ip) ? $ip : 'unknow';
    }
}
