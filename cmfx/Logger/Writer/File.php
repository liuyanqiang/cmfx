<?php
namespace Cmfx\Logger\Writer;

use Cmfx\Logger;
use Cmfx\Logger\WriterInterface;

/**
 * 使用文件存储日志的类
 */
class File implements WriterInterface
{
    /**
     * 日志文件
     */
    protected $_path;

    /**
     * 构造
     */
    public function __construct($path)
    {
        $this->_path = $path;
    }

    /**
     * 写日志信息到文件中
     * @param array 日志信息
     */
    public function write($data)
    {
        $fp = fopen($this->_path, 'ab');

        if (!$fp) {
            throw new \Cmfx\Logger\Exception("Can't open log file at '{$this->_path}'");
        }

        fwrite($fp, PHP_EOL);
        fwrite($fp, date('Y-m-d H:i:s', $data['timestamp']));
        fwrite($fp, ' [');
        fwrite($fp, Logger::getLevelString($data['level']));
        fwrite($fp, ']');
        fwrite($fp, PHP_EOL);

        if (is_string($data['message'])) {
            fwrite($fp, $data['message']);
            fwrite($fp, PHP_EOL);
        } elseif (is_array($data['message'])) {
            foreach ($data['message'] as $key => $value) {
                fwrite($fp, "{$key}=>{$value}");
                fwrite($fp, PHP_EOL);
            }
        } else {
            fwrite($fp, 'message invalid');
            fwrite($fp, PHP_EOL);
        }

        fclose($fp);
    }
}
