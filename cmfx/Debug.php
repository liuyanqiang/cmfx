<?php
namespace Cmfx;

/**
 * 错误信息调试类
 */
class Debug
{
    /**
     * 设定错误和异常处理
     */
    public static function register()
    {
        register_shutdown_function('\\Cmfx\\Debug::onFatalError');
        set_error_handler('\\Cmfx\\Debug::onError');
        set_exception_handler('\\Cmfx\\Debug::onException');
    }

    /**
     * 自定义异常处理
     * @param mixed $e 异常对象
     */
    public static function onException($e)
    {
        $error            = array();
        $error['message'] = $e->getMessage();
        $trace            = $e->getTrace();
        if ('E' == $trace[0]['function']) {
            $error['file'] = $trace[0]['file'];
            $error['line'] = $trace[0]['line'];
        } else {
            $error['file'] = $e->getFile();
            $error['line'] = $e->getLine();
        }

        $error['trace'] = $e->getTraceAsString();

        header('HTTP/1.1 404 Not Found');
        header('Status: 404 Not Found');

        self::halt($error);
    }

    /**
     * 自定义错误处理
     * @param int $errno 错误类型
     * @param string $errstr 错误信息
     * @param string $errfile 错误文件
     * @param int $errline 错误行数
     */
    public static function onError($errno, $errstr, $errfile, $errline)
    {
        switch ($errno) {
            case E_ERROR:
            case E_PARSE:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
                $errorStr = "$errstr " . $errfile . " 第 $errline 行.";
                self::halt($errorStr);
                break;
            default:
                $errorStr = "[$errno] $errstr " . $errfile . " 第 $errline 行.";
                self::trace($errorStr, 'NOTICE');
                break;
        }
    }

    /**
     * 致命错误捕获
     */
    public static function onFatalError()
    {
        if ($e = error_get_last()) {
            switch ($e['type']) {
                case E_ERROR:
                case E_PARSE:
                case E_CORE_ERROR:
                case E_COMPILE_ERROR:
                case E_USER_ERROR:
                    self::halt($e);
                    break;
            }
        }
    }

    /**
     * 错误输出
     * @param mixed $error 错误
     */
    public static function halt($error)
    {
        $e = array();

        //调试模式下输出错误信息
        if (!is_array($error)) {
            $trace        = debug_backtrace();
            $e['message'] = $error;
            $e['file']    = $trace[0]['file'];
            $e['line']    = $trace[0]['line'];
            ob_start();
            debug_print_backtrace();
            $e['trace'] = ob_get_clean();
        } else {
            $e = $error;
        }

        echo $e['message'] . PHP_EOL . 'FILE: ' . $e['file'] . '(' . $e['line'] . ')' . PHP_EOL;
        if (isset($e['trace'])) {
            echo $e['trace'];
        }
        exit();
    }

    /**
     * 添加和获取页面Trace记录
     * @param string $value 变量
     * @param string $level 日志级别(或者页面Trace的选项卡)
     * @param boolean $record 是否记录日志
     */
    public static function trace($value, $level = 'DEBUG', $record = false)
    {
        static $_trace = array();

        $info  = print_r($value, true);
        $level = strtoupper($level);

        if (!isset($_trace[$level])) {
            $_trace[$level] = array();
        }

        echo $info;

        $_trace[$level][] = $info;
    }

}
