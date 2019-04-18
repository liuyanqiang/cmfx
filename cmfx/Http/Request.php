<?php
namespace Cmfx\Http;

/**
 * 扩展框架的Request类
 */
class Request extends \Phalcon\Http\Request
{
    /**
     * 获取 _GET 参数
     * @param string $name 参数名
     * @param string $filter 过滤规则的名称或正则字符串
     * @param mixed $defaultValue 默认值
     * @return mixe 验证后的参数值
     */
    public function G($name = null, $filter = null, $defaultValue = null)
    {
        return $this->_R($_GET, $name, $filter, $defaultValue);
    }

    /**
     * 获取 _POST 参数
     * @param string $name 参数名
     * @param string $filter 过滤规则的名称或正则字符串
     * @param mixed $defaultValue 默认值
     * @return mixe 验证后的参数值
     */
    public function P($name = null, $filter = null, $defaultValue = null)
    {
        return $this->_R($_POST, $name, $filter, $defaultValue);
    }

    /**
     * 获取参数值的通用私有方法
     * @param array $source 来源 ($_GET, $_POST, $_COOKIE 等)
     * @param string $name 参数名
     * @param string $filter 过滤规则的名称或正则字符串
     * @param mixed $defaultValue 默认值
     * @return mixe 验证后的参数值
     */
    protected function _R(&$source, $name, $filter, $defaultValue)
    {
        if (null == $name) {
            return $source;
        }

        if (!isset($source[$name])) {
            return $defaultValue;
        }

        static $fo;
        if (!isset($fo)) {
            $fo = new \Cmfx\Filter();
        }

        $value = $fo->sanitize($source[$name], $filter);
        return (null !== $value) ? $value : $defaultValue;
    }
}
