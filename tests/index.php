<?php
/**
 * 代码测试入口 (命令行执行)
 */

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
define('TEST_PATH', realpath('.'));
define('PROJECT_NAME', 'tests');
define('PROJECT_PATH', dirname(TEST_PATH));
define('APP_PATH', PROJECT_PATH . '/app');
define('CMF_PATH', PROJECT_PATH . '/cmfx');
define('DATA_PATH', PROJECT_PATH . '/data');
define('CACHE_PATH', DATA_PATH . '/cache');

// 引入配置类和加载类文件
require CMF_PATH . '/Config.php';
require CMF_PATH . '/Loader.php';

// 读取配置
$configFile = TEST_PATH . '/config/main.php';
$config     = \Cmfx\Config::load($configFile);

// 命令行参数
$config['argv'] = $argv;

// 类加载注册
$loader = \Cmfx\Loader::getInstance($config);
$loader->register();

// 启动应用
$starter = \Cmfx\Starter::getInstance($config);
$starter->run();
