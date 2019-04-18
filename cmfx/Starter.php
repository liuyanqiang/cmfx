<?php
namespace Cmfx;

/**
 * 应用程序启动器抽象类
 */
abstract class Starter implements StarterInterface
{
    /**
     * 创建 Starter 子类的实例并将其返回
     */
    public static function getInstance($config)
    {
        $class   = $config['starter'];
        $starter = new $class($config);

        if (!($starter instanceof StarterInterface)) {
            throw new Exception('配置项 [starter] 错误，未实现 \Cmfx\StarterInterface 接口');
        }

        return $starter;
    }

    /**
     * 应用配置
     */
    protected $_config;

    /**
     * 构造函数
     */
    public function __construct($config = null)
    {
        $this->_config = $config;
    }
}
