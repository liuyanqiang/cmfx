<?php
namespace Cmfx\Starter;

/**
 * GroupMvc 应用启动类
 */
class GroupMvc extends \Cmfx\Starter
{
    public function run()
    {
        // 注入依赖
        $di = new \Phalcon\DI\FactoryDefault();

        // 配置
        $config = $this->_config;
        $di->set('config', $config, true);

        // 创建服务
        foreach ($config['services'] as $name => $service) {
            $definition = $service['definition'];
            if (is_array($definition)) {
                $definition = function () use ($definition) {
                    $builder = new \Cmfx\Di\Builder($this);
                    return $builder->build($definition);
                };
            }
            $shared = isset($service['shared']) && $service['shared'];
            $di->set($name, $definition, $shared);
        }

        // 执行初始调用
        $builder = new \Cmfx\Di\Builder($di);
        $builder->init($config['inits']);

        // 设置路由, 使「控制器」可按文件夹分组
        $r1 = $di['router']->add(
            '#^/([\\w0-9\\_\\-]+/[\\w0-9\\_\\-]+)[/]{0,1}$#u',
            [
                'controller' => 1
            ]
        );
        $r2 = $di['router']->add(
            '#^/([\\w0-9\\_\\-]+/[\\w0-9\\_\\-]+)/([\\w0-9\\.\\_]+)(/.*)*$#u',
            [
                'controller' => 1,
                'action'     => 2,
                'params'     => 3,
            ]
        );
        $checkController = function($uri, $route) use ($config) {
            $parts = explode('/', ltrim($uri, '/'));
            $path = $config['dirs']['controllers'] . '/' . ucfirst($parts[0]);
            return is_dir($path);
        };
        $r1->beforeMatch($checkController);
        $r2->beforeMatch($checkController);

        // 开始处理流程
        $response = $di['application']->handle();
        $response->send();
    }
}
