<?php

/**
 * 获取字典项数值
 * @param string $key 字典项的键
 * @return 返回字典项数据
 */
function dict($key)
{
    return \UI\Models\Dict::KV($key);
}

/**
 * 获取片段内容
 * @param string $key 键
 * @param integer $getType 获取类型 (1,标题; 2,内容; 3,标题和内容)
 * @return 返回片段内容
 */
function fragment($key, $getType = 2)
{
    return \UI\Models\Fragment::FG($key, $getType);
}

/**
 * 获取第一行数据
 * @param string $text 文本内容
 * @return string 返回第一行的数据
 */
function firstLine($text)
{
    if (empty($text) || !is_string($text)) {
        return '';
    }
    $arr = preg_split('/[\r\n]+/', trim($text));
    return $arr[0];
}

/**
 * 获取第 N 行数据 (从 0 开始计数，空行计算在内)
 * @param string $text 文本内容
 * @param integer $n 第几行
 * @return string 返回第指定行的数据
 */
function getLine($text, $n = 0)
{
    if (empty($text) || !is_string($text)) {
        return '';
    }
    $arr = preg_split('/(\r\n|\r|\n)/', $text);
    if ($n >= count($arr)) {
        return '';
    }
    return $arr[$n];
}

/**
 * 换行替换为 <br/> 标签
 * @note: 原生的 nl2br 函数是在换行前面添加 <br/> 标签，非替换
 * @param string $string 要处理的字符串
 * @return string 替换后的字符串
 */
function nl2br2($string)
{
    $string = str_replace(["\r\n", "\r", "\n"], '<br/>', $string);
    return $string;
}

/**
 * 扫描获取目录中的文件
 *
 * @param array $options 查找选项
 *      'recursive'     (可选) 是否递归查找 (默认 false)
 *      'extensions'    (可选) 匹配的文件扩展名 (格式: jpg,bmp,png )
 *      'regex'         (可选) 正则匹配
 *      'callable'      (可选) 回调方法匹配
 *
 * @return array 返回目录下的文件列表
 *      如果没有设置匹配规则，返回所有文件;
 *      如果有多项匹配规则，必须同时满足才返回
 */
function scanDirectory($dir, array $options = [])
{
    // 非目录返回空
    if (!is_dir($dir)) {
        return [];
    }

    // 存储匹配的文件名称
    $arr = [];

    // 扫描目录
    $files = array_diff(scandir($dir), ['.', '..']);

    // 相对路径
    $relative = empty($options['relative']) ? '' : $options['relative'];

    // 依次匹配各文件
    foreach ($files as $f) {
        // 目录
        if (is_dir($dir . DIRECTORY_SEPARATOR . $f)) {
            if (isset($options['recursive']) && $options['recursive'] === true) {
                // 递进相对路径
                $options['relative'] = empty($relative) ? $f : ($relative . DIRECTORY_SEPARATOR . $f);
                // 递归扫描
                $arr = array_merge($arr, scanDirectory($dir . DIRECTORY_SEPARATOR . $f, $options));
                // 恢复相对路径
                $options['relative'] = $relative;
            }
        }
        // 文件
        else {
            // 是否匹配
            $match = true;

            // 扩展名匹配 (格式: 'jpg,gif,png')
            if ($match && !empty($options['extensions'])) {
                $ext = str_replace(',', '|\.', $options['extensions']);
                if (!preg_match("/(\.{$ext})$/i", $f)) {
                    $match = false;
                }
            }

            // 正则匹配
            if ($match && !empty($options['regex'])) {
                if (!preg_match($options['regex'], $f)) {
                    $match = false;
                }
            }

            // callable 匹配
            if ($match && isset($options['callback']) && is_callable($options['callback'])) {
                if (!call_user_func($options['callback'], $f)) {
                    $match = false;
                }
            }

            // 符合所有条件
            if ($match) {
                $arr[] = empty($options['relative']) ? $f : ($options['relative'] . DIRECTORY_SEPARATOR . $f);
            }
        }
    }

    return $arr;
}

/**
 * 移动端判断 (不一定准确)
 */
function isMobile()
{
    // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
    if (isset($_SERVER['HTTP_X_WAP_PROFILE'])) {
        return true;
    }

    // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
    if (isset($_SERVER['HTTP_VIA'])) {
        // 找不到为flase,否则为true
        return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
    }

    // 判断手机发送的客户端标志,兼容性有待提高
    if (isset($_SERVER['HTTP_USER_AGENT'])) {
        $clientkeywords = array(
            'mobileexplorer',
            'nokia',
            'iphone',
            'ipod',
            'samsung',
            'htc',
            'sony',
            'ericsson',
            'motorola',
            'sharp',
            'sie-',
            'philips',
            'panasonic',
            'alcatel',
            'lenovo',
            'blackberry',
            'meizu',
            'mot',
            'sgh',
            'lg',
            'android',
            'netfront',
            'symbian',
            'ucweb',
            'windowsce',
            'palm',
            'operamini',
            'operamobi',
            'openwave',
            'nexusone',
            'cldc',
            'midp',
            'wap',
            'mobile',
        );

        // 从HTTP_USER_AGENT中查找手机浏览器的关键字
        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
            return true;
        }
    }

    // 协议法，因为有可能不准确，放到最后判断
    if (isset($_SERVER['HTTP_ACCEPT'])) {
        // 如果只支持wml并且不支持html那一定是移动设备
        // 如果支持wml和html但是wml在html之前则是移动设备
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
            return true;
        }
    }
    return false;
}
