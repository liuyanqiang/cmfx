<?php
namespace UI\Extensions;

/**
 * redis 客户端类
 */
class Redis
{
    protected $_redis = null;
    protected $_options = [
        'host'       => '127.0.0.1',
        'port'       => 6379,
        'auth'       => null,
        'persistent' => false,
        'timeout'    => 15,
        'index'      => 0,
    ];

    public function __construct($params = null)
    {
        if (is_array($params)) {
            foreach ($params as $k => $v) {
                if (isset($this->_options[$k])) {
                    $this->_options[$k] = $v;
                }
            }
        }
    }

    protected function _connect()
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
            throw new \Exception('Could not connect to the redis server ' . $host . ':' . $port);
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

    public function __call($method, $params)
    {
        $this->_connect();
        return call_user_func_array(array($this->_redis, $method), $params);
    }
}
