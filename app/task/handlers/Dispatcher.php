<?php
namespace UI\Handlers;

/**
 * 分发器的事件处理类
 */
class Dispatcher
{
    public function beforeException($event, $dispatcher, $exception)
    {
        echo $exception->getMessage();
        echo "\n";
        return false;
    }
}
