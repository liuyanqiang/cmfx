<?php
namespace Cmfx\Cache\Adapter;

use Cmfx\Cache\Cache;
use Cmfx\Cache\Exception;

/**
 * memcache 缓存类
 */
class Memcache extends Cache
{
    protected $_memcache = null;
    protected $_options  = [
        'host'       => '127.0.0.1',
        'port'       => 11211,
        'persistent' => false,
    ];

    /**
     * 连接 memcache
     */
    public function _connect()
    {
        if (is_object($this->_memcache)) {
            return;
        }

        extract($this->_options);

        $this->_memcache = new \Memcache();
        if ($persistent) {
            $success = $this->_memcache->pconnect($host, $port);
        } else {
            $success = $this->_memcache->connect($host, $port);
        }

        if (!$success) {
            throw new Exception('Could not connect to the memcache server ' . $host . ':' . $port);
        }
    }

    /**
     * 向连接池中添加一个memcache服务器
     * @param string $host 主机地址
     * @param integer $port 端口
     * @param boolean $persistent 是否持久连接
     * @return boolean 是否添加服务器成功
     */
    public function addServer($host, $port, $persistent = false)
    {
        $this->_connect();
        $success = $this->_memcache->addServer($host, $port, $persistent);
        return $success;
    }

    /**
     * 获取缓存内容
     * @param string $key 缓存名称
     * @return boolean|string 返回缓存内容,不存在返回 false
     */
    public function get($key)
    {
        $this->_connect();
        $lastKey = $this->_prefix . $key;
        return $this->_memcache->get($lastKey);
    }

    /**
     * 保存缓存内容
     * @param string $key 缓存名称
     * @param string $content 缓存内容
     * @param int $lifetime 生命周期 (单位秒, 0 表示不过期)
     * @return boolean 保存成功返回 true, 否则返回 false
     */
    public function set($key, $content, $lifetime = 0)
    {
        $this->_connect();
        $lastKey = $this->_prefix . $key;

        $success = $this->_memcache->set($lastKey, $content, 0, $lifetime);
        return $success;
    }

    /**
     * 删除缓存
     * @param string $key 缓存名称
     * @return boolean 删除成功返回 true, 否则返回 false
     */
    public function del($key)
    {
        $this->_connect();
        $lastKey = $this->_prefix . $key;
        return $this->_memcache->delete($lastKey);
    }

    /**
     * 获取 hash 缓存内容
     * @param string $key 缓存名称
     * @param string $hashKey 缓存项名称
     * @return boolean|string 返回缓存内容,不存在返回 false
     */
    public function hget($key, $hashKey)
    {
        $cache = $this->get($key);
        if (false !== $cache) {
            $hash = unserialize($cache);
            if (isset($hash[$hashKey])) {
                return $hash[$hashKey];
            }
        }
        return false;
    }

    /**
     * 保存 hash 缓存内容
     * @param string $key 缓存名称
     * @param string $hashKey 缓存项名称
     * @param string $content 缓存内容
     * @param int $lifetime 生命周期 (单位秒, 0 表示不过期)
     * @return boolean 保存成功返回 true, 否则返回 false
     */
    public function hset($key, $hashKey, $content, $lifetime = 0)
    {
        $cache = $this->get($key);
        $hash  = ($cache === false) ? [] : unserialize($cache);

        $hash[$hashKey]     = $content;
        $hash['__EXPIRE__'] = $lifetime > 0 ? $lifetime + time() : 0;

        return $this->set($key, serialize($hash), $lifetime);
    }

    /**
     * 删除 hash 缓存项
     * @param string $key 缓存名称
     * @param string $hashKey 缓存项名称
     * @return boolean 删除成功返回 true, 否则返回 false
     */
    public function hdel($key, $hashKey)
    {
        $cache = $this->get($key);

        if (false === $cache) {
            return true;
        }

        $hash = unserialize($cache);

        if (isset($hash[$hashKey])) {
            unset($hash[$hashKey]);
        }

        if (!isset($hash['__EXPIRE__'])) {
            $hash['__EXPIRE__'] = 0;
        }

        if (count($hash) == 1) {
            return $this->del($key);
        }

        return $this->set($key, serialize($hash), $hash['__EXPIRE__']);
    }

}
