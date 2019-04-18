<?php
namespace UI\Tests\Tasks;

use Phalcon\Cli\Task as BaseTask;

/**
 * 帮助
 */
class HelpTask extends BaseTask
{
    /**
     * 主方法
     */
    public function mainAction($params, $options)
    {
        $this->helpAction($params, $options);
    }

    /**
     * 帮助信息
     */
    public function helpAction($params, $options)
    {
        echo <<<EOT
项目代码测试

用法:
    php {$options['entry']} <command> [options]

命令:
    list    显示可测试的单元列表
    test    执行测试
    help    显示帮助列表

命令详细帮助:
    php {$options['entry']} help <command>

EOT;
    }

    /**
     * 列表帮助
     */
    public function listAction($params, $options)
    {
        echo <<<EOT
列表命令

用法:
    php {$options['entry']} list [unit] [--expand]

参数:
    unit        显示指定的测试单元
    --expand    显示测试单元中的具体测试项

EOT;
    }

    /**
     * 测试帮助
     */
    public function testAction($params, $options)
    {
        echo <<<EOT
测试命令

用法:
    php {$options['entry']} test [unit] [item]

参数:
    unit        测试某单元中的所有测试项
    unit item   测试某单元中的指定测试项

EOT;
    }
}
