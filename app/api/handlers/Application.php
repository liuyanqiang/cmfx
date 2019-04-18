<?php
namespace UI\Handlers;

/**
 * 应用程序的事件处理类
 */
class Application
{
    /**
     * 在向浏览器发送数据之前，去掉未授权的标签及内容
     */
    public function beforeSendResponse($event, $application, $response)
    {
    }
}
