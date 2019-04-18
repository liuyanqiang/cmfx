<?php
namespace Cmfx\Di;

/**
 * 解析服务配置, 并创建各项服务声明的实例的类
 *
 * 在 services.php 中定义的服务，其中的 definition 部分，通过此类创建为实例，
 * 然后指定给新 service, 再在 di 中注册。
 *
 * 用于代替 \Phalcon\Di\Service\Builder
 */
class Builder
{
    /**
     * 依赖
     */
    protected $_di = null;

    /**
     * 构造函数
     */
    public function __construct($di)
    {
        $this->_di = $di;
    }

    /**
     * 根据服务声明创建实例
     * @param mixed $definition 服务的声明部分
     * @return 如果 $definition 是数组，返回创建后的实例，否则直接返回 $definition
     */
    public function build($definition)
    {
        return $this->_parse($definition);
    }

    /**
     * 根据配置声明执行初始调用
     * @param mixed $definition 执行调用的配置声明
     */
    public function init($definition)
    {
        $this->_parse($definition);
    }

    /**
     * 替归解析
     * @param mixed $data 待解析的定义数据
     * @return 返回根据不同类型分别解析后的数值
     */
    protected function _parse($data)
    {
        if (is_string($data)) {
            return $this->_parseReference($data);
        }

        if (!is_array($data)) {
            return $data;
        }

        if (isset($data['type']) && isset($data['base'])) {
            return $this->_parseNode($data);
        }

        foreach ($data as $key => $value) {
            $data[$key] = $this->_parse($value);
        }

        return $data;
    }

    /**
     * 解析节点
     * @param array $node 服务定义中的节点 (带有 type 和 base 键值的 数组)
     * @param object $instance 最新创建的实例 (作为主体进行方法调用和属性设置)
     * @return 返回创建的实例，或者调用的方法或函数的返回值
     */
    protected function _parseNode($node, $instance = null)
    {
        $type = $node['type'];
        $base = $node['base'];

        // 类/服务
        if ($type == 'class' || $type == 'service') {

            // 创建类实例
            $className = $base;

            $params = [];
            if (isset($node['params'])) {
                $params = $this->_parse($node['params']);
            }

            $instance = $this->_di->get($className, $params);

            if (!is_object($instance)) {
                throw new Exception("创建 '{$className}' 的服务实例实败");
            }

            // calls
            if (isset($node['calls'])) {
                $calls = $node['calls'];
                if (!is_array($calls)) {
                    throw new Exception("'{$className}' 的 'calls' 配置必须为数组");
                }
                foreach ($calls as $methodCall) {
                    $this->_parseNode($methodCall, $instance);
                }
            }

            // properties
            if (isset($node['properties'])) {
                $properties = $node['properties'];
                if (!is_array($properties)) {
                    throw new Exception("'{$className}' 的 'properties' 配置必须为数组");
                }
                foreach ($properties as $propertySetter) {
                    $this->_parseNode($propertySetter, $instance);
                }
            }

            // return
            if (isset($node['return'])) {
                $instance = $this->_parseNode($node['return'], $instance);
            }

            // 返回实例
            return $instance;
        }

        // 方法
        if ($type == 'method') {

            if (is_null($instance)) {
                throw new Exception("对 null 进行方法 '{$base}'' 调用");
            }

            $callable = [$instance, $base];

            if (!is_callable($callable)) {
                throw new Exception("方法 '{$base}'' 不可调用");
            }

            $params = [];
            if (isset($node['params'])) {
                $params = $this->_parse($node['params']);
            }

            return call_user_func_array($callable, $params);
        }

        // 函数
        if ($type == 'function') {
            $params = [];
            if (isset($node['params'])) {
                $params = $this->_parse($node['params']);
            }
            return call_user_func_array($base, $params);
        }

        // 属性
        if ($type == 'property') {
            if (is_null($instance)) {
                throw new Exception("对 null 的属性 '{$base}' 进行赋值");
            }

            if (!isset($node['value'])) {
                throw new Exception("属性 '{$base}' 未指定 'value' 值");
            }

            // 获取属性值
            $value = $this->_parse($node['value']);

            // 属性赋值
            $instance->$base = $value;

            return $value;
        }

        // 其他
        return $node;
    }

    /**
     * 解析字符串引用
     * 例如把以 'config:' 开头的数据转换为配置中的对应项
     * @param string $data 待解析的字符串
     * @return mixed 如果是引用，返回引用值; 否则原值返回
     */
    protected function _parseReference($data)
    {
        if (strncasecmp($data, 'config://', 9) === 0) {
            $path   = substr($data, 9);
            $config = $this->_di->getShared('config');
            $data   = $config->path($path);
            if (is_null($data)) {
                throw new Exception("配置项 '{$data}' 未定义");
            }
            return $data;
        } elseif (strncasecmp($data, 'service://', 10) === 0) {
            $name = substr($data, 10);
            return $this->_di->get($name);
        }

        return $data;
    }

}
