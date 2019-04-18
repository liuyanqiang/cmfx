<?php
namespace Cmfx;

use Cmfx\Logger\WriterInterface;

/**
 * Cmfx\Logger
 *
 * 日志类
 *
 * <code>
 * use Cmfx\Logger;
 * use Cmfx\Logger\Writer\File as FileWriter;
 *
 * $writer = new FileWriter('./logs/sys.log');
 * $logger = new Logger($writer);
 *
 * $logger->log("This is a message");
 * $logger->log(Logger::ERROR, "This is an error");
 * $logger->error("This is another error");
 * </code>
 */
class Logger
{
    /**
     * 日志级别
     */
    const DEBUG     = 7;
    const INFO      = 6;
    const NOTICE    = 5;
    const WARNING   = 4;
    const ERROR     = 3;
    const CRITICAL  = 2;
    const ALERT     = 1;
    const EMERGENCY = 0;

    /**
     * 日志的记录器
     */
    protected $_writer;

    /**
     * 记录等级
     */
    protected $_logLevel = self::DEBUG;

    /**
     * 获取级别名称
     * @param integer $level 级别
     * @return string 级别名称
     */
    public static function getLevelString($level)
    {
        switch ($level) {
            case self::DEBUG:
                return 'DEBUG';
            case self::INFO:
                return 'INFO';
            case self::NOTICE:
                return 'NOTICE';
            case self::WARNING:
                return 'WARNING';
            case self::ERROR:
                return 'ERROR';
            case self::CRITICAL:
                return 'CRITICAL';
            case self::ALERT:
                return 'ALERT';
            case self::EMERGENCY:
                return 'EMERGENCY';
            default:
                return 'DEBUG';
        }
    }

    /**
     * 获取所有级别
     * @return array 级别编号和名称的映射
     */
    public static function getLevels()
    {
        return array(
            self::EMERGENCY => 'EMERGENCY',
            self::ALERT     => 'ALERT',
            self::CRITICAL  => 'CRITICAL',
            self::ERROR     => 'ERROR',
            self::WARNING   => 'WARNING',
            self::NOTICE    => 'NOTICE',
            self::INFO      => 'INFO',
            self::DEBUG     => 'DEBUG',
        );
    }

    /**
     * 构造函数
     * @param WriterInterface $writer
     */
    public function __construct($writer = null)
    {
        if ($writer) {
            $this->setWriter($writer);
        }
    }

    /**
     * 设置记录等级
     */
    public function setLogLevel($level)
    {
        $this->_logLevel = $level;
    }

    /**
     * 返回当前记录等级
     */
    public function getLogLevel()
    {
        return $this->_logLevel;
    }

    /**
     * 设置记录器
     * @param WriterInterface $writer
     */
    public function setWriter($writer)
    {
        if (!($writer instanceof WriterInterface)) {
            throw new Exception('writer 必须是实现 \Cmfx\Logger\WriterInterface 接口的对象');
        }
        $this->_writer = $writer;
    }

    /**
     * 获取记录器
     * @return WriterInterface $writer
     */
    public function getWriter()
    {
        return $this->_writer;
    }

    /**
     * 用上下文信息替换记录信息中的占位符
     * @param string $message 信息
     * @param array $context 上下文内容
     * @return string 替换占位符后的信息
     */
    protected function _interpolate($message, array $context = array())
    {
        // 构建一个花括号包含的键名的替换数组
        $replace = array();
        foreach ($context as $key => $val) {
            $replace['{' . $key . '}'] = $val;
        }

        // 替换记录信息中的占位符，最后返回修改后的记录信息。
        return strtr($message, $replace);
    }

    /**
     * 保存日志
     * @param int $level 信息级别 (默认 DEBUG 级别)
     * @param string $message 信息
     * @param array $context 上下文内容
     */
    public function log($level, $message = null, array $context = array())
    {
        if (is_string($level)) {
            $msg     = $level;
            $level   = is_int($message) ? $message : self::DEBUG;
            $message = $msg;
        }

        if ($this->_writer === null) {
            throw new Exception('Logger 未设置 writer 实例');
        }

        $message = $this->_interpolate($message, $context);

        if ($this->_logLevel >= $level) {
            $this->_writer->write([
                'level'     => $level,
                'message'   => $message,
                'timestamp' => time(),
            ]);
        }
    }

    /**
     * 记录各级别的信息
     */
    public function debug($message, array $context = array())
    {
        $this->log(self::DEBUG, $message, $context);
    }

    public function info($message, array $context = array())
    {
        $this->log(self::INFO, $message, $context);
    }

    public function notice($message, array $context = array())
    {
        $this->log(self::NOTICE, $message, $context);
    }

    public function warning($message, array $context = array())
    {
        $this->log(self::WARNING, $message, $context);
    }

    public function error($message, array $context = array())
    {
        $this->log(self::ERROR, $message, $context);
    }

    public function critical($message, array $context = array())
    {
        $this->log(self::CRITICAL, $message, $context);
    }

    public function alert($message, array $context = array())
    {
        $this->log(self::ALERT, $message, $context);
    }

    public function emergency($message, array $context = array())
    {
        $this->log(self::EMERGENCY, $message, $context);
    }
}
