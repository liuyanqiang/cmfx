<?php
namespace Cmfx;

/**
 * 自动加载类
 */
class Loader extends \Phalcon\Loader
{
    /**
     * 单一实例
     */
    private static $_instance;

    /**
     * 额外的 autoloader
     */
    protected $_loaders = [];

    /**
     * 获取单一实例的静态方法
     * @param \Cmfx\Config $config 项目配置
     * @return single instance of Loader
     */
    public static function getInstance(Config $config)
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new Loader($config);
        }
        return self::$_instance;
    }

    /**
     * 私有构造
     * @param \Cmfx\Config $config 项目配置
     */
    private function __construct(Config $config)
    {
        foreach ($config['register'] as $r) {
            $this->{$r['method']}($r['params']);
        }
    }

    /**
     * 注册额外的 autoloader
     * 如果内置的加载方法无效则调用额外注册的 autoloader
     */
    public function registerLoaders(array $loaders, $merge = false)
    {
        if ($merge) {
            $this->_loaders = array_merge($this->_loaders, $loaders);
        } else {
            $this->_loaders = $loaders;
        }
        return $this;
    }

    /**
     * 返回额外的 autoloaders
     */
    public function getLoaders()
    {
        return $this->_loaders;
    }

    /**
     * 扩展父类的 autoLoad 方法，以支持外部附加的 autoloaders
     */
    public function autoLoad($className)
    {
        $find = parent::autoLoad($className);
        if ($find) {
            return true;
        }

        $eventsManager = $this->_eventsManager;
        if (is_object($eventsManager)) {
            $eventsManager->fire("loader:beforeExtarnalLoader", $this, $className);
        }

        foreach ($this->_loaders as $loader) {
            if (call_user_func_array($loader, [$className])) {
                if (is_object($eventsManager)) {
                    $eventsManager->fire("loader:extarnalFound", $this, $className);
                }
                return true;
            }
        }

        if (is_object($eventsManager)) {
            $eventsManager->fire("loader:afterExtarnalLoader", $this, $className);
        }

        return false;
    }
}
