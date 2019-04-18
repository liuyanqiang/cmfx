<?php
namespace Cmfx\Starter;

/**
 * Mvc 应用启动类
 */
class Mvc extends \Cmfx\Starter
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

        // 开始处理流程
        $response = $di['application']->handle();
        $response->send();
    }
}
