<?php
namespace UI\Tests\Units;

use \Cmfx\Cache\Adapter\File as FileCache;
use \Cmfx\Cache\Adapter\Redis as RedisCache;
use \Cmfx\Test\Unit as BaseUnit;

/**
 * 测试 \Cmfx\Cache 相关部分的代码
 */
class CacheUnit extends BaseUnit
{
    protected $_adapters;

    /**
     * 初始测试
     */
    public function initialize()
    {
        $this->_adapters = [];

        $this->_adapters['redis'] = new RedisCache([
            'prefix' => 'test-',
            'host'   => 'redis',
        ]);

        /*
        $this->_adapters['memcache'] = new MemcacheCache([
        'prefix' => 'test-',
        'host'   => '192.168.0.101',
        ]);
         */

        $dir = '/tmp/cmfx-cache';
        mkdir($dir);

        $this->_adapters['file'] = new FileCache([
            'prefix' => 'test',
            'dir'    => $dir,
        ]);
    }

    /**
     * 缓存 set 和 get 测试
     */
    public function setgetTest()
    {
        foreach ($this->_adapters as $key => $adapter) {
            $data = $adapter->get('notexists');
            $this->assertTrue($data === false, $key . ', 通过不存在键名获取缓存返回 false 验证成功');

            $success = $adapter->set('name', 'luyamin');
            $this->assertTrue($success === true, $key . ', 保存缓存成功');

            $name = $adapter->get('name');
            $this->assertTrue($name === 'luyamin', $key . ', 获取缓存成功', $name);

            $success = $adapter->set('name', 'lupeiyu');
            $this->assertTrue($success === true, $key . ', 重写缓存成功');

            $name = $adapter->get('name');
            $this->assertTrue($name === 'lupeiyu', $key . ', 验证重写的缓存值成功', $name);

            $success = $adapter->del('name');
            $this->assertTrue($success === true, $key . ', 删除缓存成功');

            $name = $adapter->get('name');
            $this->assertTrue($name === false, $key . ', 获取删除后的缓存返回 false 验证成功');

            $success = $adapter->set('tel', '4005008000', 1);
            $this->assertTrue($success === true, $key . ', 保存带有 lifetime 的缓存成功');

            $data = $adapter->get('tel');
            $this->assertTrue($data === '4005008000', $key . ', 获取带有 lifetime 的缓存成功', $data);

            sleep(1);

            $data = $adapter->get('tel');
            $this->assertTrue($data === false, $key . ', 缓存 lifetime 过期验证成功');
        }
    }

    /**
     * 缓存 hset 和 hget 测试
     */
    public function hashTest()
    {
        foreach ($this->_adapters as $key => $adapter) {
            $data = $adapter->hget('notexists', 'name');
            $this->assertTrue($data === false, $key . ', hash, 通过不存在键名获取缓存返回 false 验证成功');

            $success = $adapter->hset('mien', 'name', 'luyamin');
            $this->assertTrue($success === true, $key . ', hash, 保存缓存成功');

            $name = $adapter->hget('mien', 'name');
            $this->assertTrue($name === 'luyamin', $key . ', hash, 获取缓存成功', $name);

            $data = $adapter->hget('mien', 'notexists');
            $this->assertTrue($data === false, $key . ', hash, 通过不存在hash项获取缓存返回 false 验证成功');

            $success = $adapter->hset('mien', 'name', 'lupeiyu');
            $this->assertTrue($success === true, $key . ', hash, 重写缓存成功');

            $name = $adapter->hget('mien', 'name');
            $this->assertTrue($name === 'lupeiyu', $key . ', hash, 验证重写的缓存值成功', $name);

            $success = $adapter->del('mien');
            $this->assertTrue($success === true, $key . ', hash, 删除缓存成功');

            $name = $adapter->hget('mien', 'name');
            $this->assertTrue($name === false, $key . ', hash, 获取删除后的缓存返回 false 验证成功');

            $success = $adapter->hset('mien', 'name', 'lupeiyu');
            $success = $adapter->hset('mien', 'tel', '4005008000', 1);
            $this->assertTrue($success === true, $key . ', hash, 保存带有 lifetime 的缓存成功');

            $success = $adapter->hdel('mien', 'name');
            $this->assertTrue($success === true, $key . ', hash, 删除hash项成功');

            $name = $adapter->hget('mien', 'name');
            $this->assertTrue($name === false, $key . ', hash, 获取删除后的hash项返回 false 验证成功');

            $data = $adapter->hget('mien', 'tel');
            $this->assertTrue($data === '4005008000', $key . ', hash, 获取带有 lifetime 的缓存成功', $data);

            sleep(1);

            $data = $adapter->hget('mien', 'tel');
            $this->assertTrue($data === false, $key . ', hash, 缓存 lifetime 过期验证成功');
        }
    }

    /**
     * 清理测试文件
     */
    public function finish()
    {
        $this->_rmdir('/tmp/cmfx-cache');
    }

    /**
     * 递归删除目录
     */
    protected function _rmdir($src)
    {
        $dir = opendir($src);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                $full = $src . '/' . $file;
                if (is_dir($full)) {
                    $this->_rmdir($full);
                } else {
                    unlink($full);
                }
            }
        }
        closedir($dir);
        rmdir($src);
    }
}
