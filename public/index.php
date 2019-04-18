<?php
/**
 * 应用入口
 */

// 环境模式
define('ENVIRONMENT', isset($_SERVER['CMFX_ENV']) ? $_SERVER['CMFX_ENV'] : 'development');

// 报错等级
if (true || ENVIRONMENT === 'development') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
}

// 路径常量
define('PUBLIC_PATH', realpath('.'));
define('PROJECT_PATH', dirname(PUBLIC_PATH));
define('APP_PATH', PROJECT_PATH . '/app');
define('CMF_PATH', PROJECT_PATH . '/cmfx');
define('DATA_PATH', PROJECT_PATH . '/data');
define('CACHE_PATH', DATA_PATH . '/cache');

// 项目映射 (域名或目录)
$map = [
    'api.' => 'api'
];

// 匹配项目
foreach ($map as $key => $project) {
    if ($key == '_') {
        continue;
    }
    if (substr($key, -1) == '.') {
        if (strncasecmp($key, strtolower($_SERVER['HTTP_HOST']), strlen($key)) === 0) {
            define('PROJECT_NAME', $project);
            define('BASE_URI', '');
            break;
        }
    } elseif ($key[0] == '/') {

        $url = isset($_GET['_url']) ? $_GET['_url'] : '';
        $key = rtrim($key, '/');
        $len = strlen($key);

        if (strncasecmp($key, $url, $len) === 0) {

            $url = substr($url, $len);
            if (empty($url)) {
                $url = '/';
            }
            if ($url[0] != '/') {
                continue;
            }

            $_GET['_url'] = $url;

            define('BASE_URI', $key);
            define('PROJECT_NAME', $project);
            break;
        }
    }
}

// 无匹配则设置为默认项目
defined('PROJECT_NAME') || define('PROJECT_NAME', $map['_']);
defined('BASE_URI') || define('BASE_URI', '');

// 引入配置类和加载类文件
require CMF_PATH . '/Config.php';
require CMF_PATH . '/Loader.php';

// 读取配置
$configFile = APP_PATH . '/' . PROJECT_NAME . '/config/main.php';
$cachePath  = (ENVIRONMENT === 'production') ? CACHE_PATH : null;
$config     = \Cmfx\Config::load($configFile, $cachePath);

// 类加载注册
$loader = \Cmfx\Loader::getInstance($config);
$loader->register();

// 错误与异常捕捉
\Cmfx\Debug::register();

// 启动应用
$starter = \Cmfx\Starter::getInstance($config);
$starter->run();
