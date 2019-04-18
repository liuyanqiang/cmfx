<?php
namespace UI\Tests\Cli;

/**
 * 用于测试的控制台 Application 类
 */
class Console extends \Phalcon\Cli\Console
{
    /**
     * 设置参数
     */
    public function setArguments(array $arguments = null)
    {
        $args       = [];
        $opts       = [];
        $handleArgs = [];

        if (count($arguments)) {
            $opts['entry'] = array_shift($arguments);
        }

        foreach ($arguments as $arg) {
            if (is_string($arg)) {
                if (strncmp($arg, '--', 2) == 0) {
                    $pos = strpos($arg, '=');
                    if ($pos) {
                        $opts[trim(substr($arg, 2, $pos - 2))] = trim(substr($arg, $pos + 1));
                    } else {
                        $opts[trim(substr($arg, 2))] = true;
                    }
                } else {
                    if (strncmp($arg, '-', 1) == 0) {
                        $opts[substr($arg, 1)] = true;
                    } else {
                        $args[] = $arg;
                    }
                }
            } else {
                $args[] = $arg;
            }
        }

        if (count($args)) {
            $handleArgs['task'] = array_shift($args);
        }

        if (count($args)) {
            /**
             * 只有 help 控制器的第二个参数设置为 action
             * 其余的 (list, test) 控制器 action 强制设置为 main，第二个参数开始都归入 $params
             */
            if (isset($handleArgs['task']) && $handleArgs['task'] == 'help') {
                $handleArgs['action'] = array_shift($args);
            } else {
                $handleArgs['action'] = 'main';
            }
        }

        if (count($args)) {
            $handleArgs = array_merge($handleArgs, $args);
        }

        $this->_arguments = $handleArgs;
        $this->_options   = $opts;

        return $this;
    }
}
