<?php
namespace Cmfx\Cache\Adapter;

use Cmfx\Cache\Cache;
use Cmfx\Cache\Exception;

/**
 * file 缓存类
 */
class File extends Cache
{
    protected $_options = [
        'dir' => '/tmp',
    ];

    /**
     * 构造函数
     */
    public function __construct(array $options = null)
    {
        // 保存配置
        parent::__construct($options);

        // 检查 cache 目录
        $this->_options['cacheDir'] = rtrim($this->_options['dir'], '/');

        if (!is_dir($this->_options['cacheDir'])) {
            throw new Exception('Cache directory must be specified with the option dir');
        }

        // 前缀
        if (preg_match('/[^a-zA-Z0-9_\.-]+/', $this->_prefix)) {
            throw new Exception('FileCache prefix should only use alphanumeric characters.');
        }

        if (!empty($this->_prefix)) {

            // 更改 cache 目录
            $cacheDir = $this->_options['cacheDir'] . '/' . $this->_prefix;
            $this->_options['cacheDir'] = $cacheDir;

            // 以前缀名称创建子目录
            if (!is_dir($cacheDir)) {
                mkdir($cacheDir);
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
        $lastKey = $this->_getLastKey($key);

        if (!file_exists($lastKey)) {
            return false;
        }

        $data = file_get_contents($lastKey);

        if ($data === false) {
            return false;
        }

        $lastContent = unserialize($data);

        if ($lastContent['lifetime'] > 0 && time() >= $lastContent['lifetime']) {
            return false;
        }

        return $lastContent['content'];
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
        $lastKey  = $this->_getLastKey($key);
        $lifetime = $lifetime > 0 ? $lifetime + time() : 0;

        $lastContent = [
            'lifetime' => $lifetime,
            'content'  => $content,
        ];

        $success = file_put_contents($lastKey, serialize($lastContent));

        return $success !== false;
    }

    /**
     * 删除缓存
     * @param string $key 缓存名称
     * @return boolean 删除成功返回 true, 否则返回 false
     */
    public function del($key)
    {
        $lastKey = $this->_getLastKey($key);
        return unlink($lastKey);
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

        $hash[$hashKey] = $content;

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
        $lastKey = $this->_getLastKey($key);

        if (!file_exists($lastKey)) {
            return true;
        }

        $data = file_get_contents($lastKey);

        if ($data === false) {
            return true;
        }

        $lastContent = unserialize($data);

        if ($lastContent['lifetime'] > 0 && time() >= $lastContent['lifetime']) {
            return true;
        }

        $hash = unserialize($lastContent['content']);

        if (isset($hash[$hashKey])) {
            unset($hash[$hashKey]);
        }

        if (empty($hash)) {
            return unlink($lastKey);
        }

        $lastContent['content'] = serialize($hash);

        $success = file_put_contents($lastKey, serialize($lastContent));

        return $success !== false;
    }

    /**
     * 获取最终的缓存健名称
     * @param string $key 缓存名称
     * @return string 缓存键完全名称
     */
    protected function _getLastKey($key)
    {
        return sprintf('%s/%s',
            $this->_options['cacheDir'],
            md5($key)
        );
    }
}
