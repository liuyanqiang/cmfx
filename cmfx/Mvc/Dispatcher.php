<?php
namespace Cmfx\Mvc;

/**
 * Dispatcher 扩展类
 * 使 Dispatcher 支持带路径('/')的控制器名称，
 * 以便按目录分组控制器代码类文件
 */
class Dispatcher extends \Phalcon\Mvc\Dispatcher
{
    /**
     * 获取 handler 类名
     */
    public function getHandlerClass()
    {
        $handlerClass = parent::getHandlerClass();

        // 如果带有 '/' 则转换为命名空间的格式，如: abc/efg -> Abc\Efg
        if (strpos($handlerClass, '/') !== false) {
            $handlerClass = preg_replace_callback(
                '#/(\w)#', 
                function ($matches) {
                    return '\\' . strtoupper($matches[1]);
                }, 
                $handlerClass
            );
        }

        return $handlerClass;
    }
}