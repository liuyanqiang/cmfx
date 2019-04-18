<?php
namespace Cmfx;

/**
 * 配置类
 *
 * 简单封装一个数组，是为了在各个对象间传递时，不至于直接传参数组，来回复制，影响效率。
 * 不使用 \Phalcon\Config 是因为它把各项值都转化为对象，没有必要，
 * 并且在需要数组类型的地方，各级对象递归调用 toArray 也是降低效率。
 */
class Config implements \ArrayAccess, \Countable
{
    /**
     * 配置数据
     */
    protected $_data;

    /**
     * 加载配置的静态方法
     * @param string $configFile 配置文件路径
     * @param string $cachePath 存储配置的缓存目录
     * @return Config 此配置类实例
     */
    public static function load($configFile, $cachePath = null)
    {
        // 初始数值
        $config    = null;
        $cacheFile = null;

        // 从缓存加载
        if (!is_null($cachePath) && is_dir($cachePath)) {
            $cacheFile = sprintf('%s/%s', $cachePath, md5($configFile));
            if (file_exists($cacheFile)) {
                $config = require $cacheFile;
            }
        }

        // 从指定的配置文件加载
        if ($config === null) {

            // 加载主配置
            $config = require $configFile;

            // 加载子项配置
            if (is_array($config['subkeys']) && is_array($config['subdirs'])) {
                foreach ($config['subkeys'] as $name) {
                    foreach ($config['subdirs'] as $dir) {
                        $file = $dir . '/' . $name . '.php';
                        if (file_exists($file)) {
                            $config[$name] = require $file;
                            break;
                        }
                    }
                }
            }

            // 保存配置到缓存
            if ($cacheFile) {
                file_put_contents($cacheFile, "<?php\nreturn " . var_export($config, true) . ';', LOCK_EX);
            }
        }

        // 创建配置实例
        $instance = new self($config);

        // 返回实例
        return $instance;
    }

    public function __construct(&$config = null)
    {
        if (!is_array($config)) {
            $this->_data = [];
        } else {
            $this->_data = &$config;
        }
    }

    public function count()
    {
        return count($this->_data);
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->_data[] = $value;
        } else {
            $this->_data[$offset] = $value;
        }
    }

    public function offsetExists($offset)
    {
        return isset($this->_data[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->_data[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->_data[$offset]) ? $this->_data[$offset] : null;
    }

    public function &toArray()
    {
        return $this->_data;
    }

    /**
     * 以路径的方式获取深层次的数组数据
     * @param string $path, 数组的键值路径, 比如: '/services/db/host'
     * @param return mixed 匹配的数值, 不存在返回 null
     */
    public function &path($path)
    {
        $path = trim($path, '/');
        if (empty($path)) {
            return $this->_data;
        }

        $parts = explode('/', $path);
        $data  = &$this->_data;

        foreach ($parts as $key) {
            if (!isset($data[$key])) {
                return null;
            }
            $data = &$data[$key];
        }

        return $data;
    }
}
