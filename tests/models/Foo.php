<?php

namespace UI\Tests\Models;

/**
 * 用于测试的 Collection 类
 */
class Foo extends \Cmfx\Mvc\Collection
{
    public function getSource()
    {
        return 'test_foo';
    }
}
