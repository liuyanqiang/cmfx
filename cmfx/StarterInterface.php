<?php

namespace Cmfx;

/**
 * 各种应用模式(mvc,api,cli)启动类的接口
 */
interface StarterInterface
{
    /**
     * 运行应用
     */
    public function run();
}
