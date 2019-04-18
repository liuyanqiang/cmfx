<?php
namespace UI\Handlers;

use Phalcon\Mvc\Dispatcher\Exception as DispatchException;

/**
 * 分发器的事件处理类
 */
class Dispatcher
{
    public function beforeException($event, $dispatcher, $exception)
    {
        if ($exception instanceof DispatchException) {
            $dispatcher->forward([
                'controller' => 'error',
                'action'     => 'e404',
            ]);
            return false;
        }
    }
}
