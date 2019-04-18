<?php
namespace Cmfx\Cache\Adapter;

use Cmfx\Cache\Cache;
use Cmfx\Cache\Exception;

/**
 * redis 缓存类
 */
class Redis extends Cache
{
    protected $_redis   = null;
    protected $_options = [
        'host'       => '127.0.0.1',
        'port'       => 6379,
        'auth'       => null,
        'persistent' => false,
        'timeout'    => 15,
        'index'      => 0,
    ];

    /**
     * 连接 redis
     */
    public function _connect()
    {
        if (is_object($this->_redis)) {
            return;
        }

        $this->_redis = new \Redis();

        extract($this->_options);

        if ($persistent) {
            $success = $this->_redis->pconnect($host, $port, $timeout);
        } else {
            $success = $this->_redis->connect($host, $port, $timeout);
        }

        if (!$success) {
            throw new Exception('Could not connect to the redis server ' . $host . ':' . $port);
        }

        if (!empty($auth)) {
            $success = $this->_redis->auth($auth);
            if (!$success) {
                throw new \Exception('Failed to authenticate with the redis server');
            }
        }

        if ($index > 0) {
            $success = $this->_redis->select($index);
            if (!$success) {
                throw new Exception('Redis server selected database failed');
            }
        }
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
        return $this->_redis->get($lastKey);
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

        $success = $this->_redis->set($lastKey, $content);

        if ($lifetime > 0) {
            $this->_redis->expire($lastKey, $lifetime);
        }

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

        $n = $this->_redis->delete($lastKey);
        return $n > 0;
    }

    /**
     * 获取 hash 缓存内容
     * @param string $key 缓存名称
     * @param string $hashKey 缓存项名称
     * @return boolean|string 返回缓存内容,不存在返回 false
     */
    public function hget($key, $hashKey)
    {
        $this->_connect();
        $lastKey = $this->_prefix . $key;
        return $this->_redis->hget($lastKey, $hashKey);
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
        $this->_connect();
        $lastKey = $this->_prefix . $key;

        $success = $this->_redis->hset($lastKey, $hashKey, $content);

        if ($lifetime > 0) {
            $this->_redis->expire($lastKey, $lifetime);
        }

        return $success !== false;
    }

    /**
     * 删除 hash 缓存项
     * @param string $key 缓存名称
     * @param string $hashKey 缓存项名称
     * @return boolean 删除成功返回 true, 否则返回 false
     */
    public function hdel($key, $hashKey)
    {
        $this->_connect();
        $lastKey = $this->_prefix . $key;

        $n = $this->_redis->hdel($lastKey, $hashKey);
        return $n !== false;
    }

}
