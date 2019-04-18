<?php
namespace Cmfx\Cache;

/**
 * 缓存接口
 */
interface CacheInterface
{
    /**
     * 获取缓存内容
     * @param string $key 缓存名称
     * @return boolean|string 返回缓存内容,不存在返回 false
     */
    public function get($key);

    /**
     * 保存缓存内容
     * @param string $key 缓存名称
     * @param string $content 缓存内容
     * @param int $lifetime 生命周期 (单位秒, 0 表示不过期)
     * @return boolean 保存成功返回 true, 否则返回 false
     */
    public function set($key, $content, $lifetime = 0);

    /**
     * 删除缓存
     * @param string $key 缓存名称
     * @return boolean 删除成功返回 true, 否则返回 false
     */
    public function del($key);

    /**
     * 获取 hash 缓存内容
     * @param string $key 缓存名称
     * @param string $hashKey 缓存项名称
     * @return boolean|string 返回缓存内容,不存在返回 false
     */
    public function hget($key, $hashKey);

    /**
     * 保存 hash 缓存内容
     * @param string $key 缓存名称
     * @param string $hashKey 缓存项名称
     * @param string $content 缓存内容
     * @param int $lifetime 生命周期 (单位秒, 0 表示不过期)
     * @return boolean 保存成功返回 true, 否则返回 false
     */
    public function hset($key, $hashKey, $content, $lifetime = 0);

    /**
     * 删除缓存
     * @param string $key 缓存名称
     * @param string $hashKey 缓存项名称
     * @return boolean 删除成功返回 true, 否则返回 false
     */
    public function hdel($key, $hashKey);
}
