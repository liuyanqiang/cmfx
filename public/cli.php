<?php
/**
 * 控制台任务入口 (命令行执行)
 */

// 判断是否命令行运行
if (PHP_SAPI !== 'cli' && !defined('STDIN')) {
    header('HTTP/1.1 403 Forbidden');
    echo 'Forbidden' . PHP_EOL;
    exit;
}

// 环境模式
define('ENVIRONMENT', isset($_SERVER['CMFX_ENV']) ? $_SERVER['CMFX_ENV'] : 'development');

// 报错等级
if (ENVIRONMENT === 'development') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
}

// 路径常量
define('PUBLIC_PATH', realpath('.'));
define('PROJECT_PATH', dirname(PUBLIC_PATH));
define('PROJECT_NAME', 'task');
define('APP_PATH', PROJECT_PATH . '/app');
define('CMF_PATH', PROJECT_PATH . '/cmfx');
define('DATA_PATH', PROJECT_PATH . '/data');
define('CACHE_PATH', DATA_PATH . '/cache');

// 引入配置类和加载类文件
require CMF_PATH . '/Config.php';
require CMF_PATH . '/Loader.php';

// 读取配置
$configFile = APP_PATH . '/' . PROJECT_NAME . '/config/main.php';
$cachePath  = (ENVIRONMENT === 'production') ? CACHE_PATH : null;
$config     = \Cmfx\Config::load($configFile, $cachePath);

// 命令行参数
$config['argv'] = $argv;

// 类加载注册
$loader = \Cmfx\Loader::getInstance($config);
$loader->register();

// 错误与异常捕捉
\Cmfx\Debug::register();

// 启动应用
$starter = \Cmfx\Starter::getInstance($config);
$starter->run();
