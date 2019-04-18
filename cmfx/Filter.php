<?php
namespace Cmfx;

/**
 * 规则过滤和验证类
 */
class Filter
{
    /**
     * 参数验证规则
     */
    protected static $_rules = [
        'int'      => ['/^[\-\+]?\d+$/'],
        'int!'     => ['/^[\-\+]?\d+$/', 'intval'],
        'absint!'  => ['/^[\-\+]?\d+$/', 'intval', 'abs'],
        'number'   => ['/^\d+$/'],
        'number!'  => ['/^\d+$/', 'intval'],
        'alphanum' => ['/^[a-zA-Z\d]+$/'],
        'bit'      => ['/^(0|1)$/'],
        'bit!'     => ['/^(0|1)$/', 'intval'],
        'phone'    => ['/^1\d{10}$/'],
        'email'    => ['/^(\w+)([\-\.]\w+)*@(\w+)([\-]\w+)*(\.\w{2,})+$/'],
    ];

    /**
     * 是否有匹配的验证规则
     * @param string $filter 过滤规则的名称或正则字符串
     * @return boolean 有匹配返回 true, 否则返回 false
     */
    public function has($filter)
    {
        if (empty($filter)) {
            return false;
        }
        if ($filter[0] == '/') {
            return false;
        }
        return isset(self::$_rules[$filter]);
    }

    /**
     * 返回过滤规则对应的正则表达式
     * @param string $filter 过滤规则的名称或正则字符串
     * @return 去掉前后 '/' 的正则式，无匹配返回 null
     */
    public function regex($filter)
    {
        if (empty($filter)) {
            return null;
        }
        if ($filter[0] == '/') {
            return trim($filter, '/');
        }
        if (isset(self::$_rules[$filter])) {
            return trim(self::$_rules[$filter][0], '/');
        }
        return $filter;
    }

    /**
     * 验证并处理数值
     * @param string $value 要验证的数据
     * @param string $filter 过滤规则的名称或正则字符串
     * @return mixed 验证并处理后的数值
     */
    public function sanitize($value, $filter)
    {
        if (is_string($filter) && !empty($filter)) {
            if ($filter[0] == '/') {
                if (!preg_match($filter, $value)) {
                    return null;
                }
            } elseif (isset(self::$_rules[$filter])) {
                $rule = &self::$_rules[$filter];
                if (!preg_match($rule[0], $value)) {
                    return null;
                }
                $len = count($rule);
                for ($i = 1; $i < $len; $i++) {
                    $value = call_user_func($rule[$i], $value);
                }
            }
        }
        return $value;
    }
}
