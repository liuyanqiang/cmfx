<?php
namespace UI\Tasks;

use Phalcon\Cli\Task as BaseTask;

/**
 * 帮助
 */
class HelpTask extends BaseTask
{
    /**
     * 显示帮助信息
     */
    public function mainAction($params, $options)
    {
        echo <<<EOT
控制台任务代码

用法:
    php cli.php task action

任务:
    hellp    hello
   
EOT;
    }
}
