<?php
/*
 * NOTE:
 * 此处为开发环境配置
 * 生产环境的配置在 production/settings.php 中
 */
return [
    'cache'   => [
        'host'   => '127.0.0.1',
        'port'   => 6379,
        'prefix' => 'wyh-',
    ],
    'db'      => [
        'adapter'  => 'mysql',
        'host'     => '127.0.0.1',
        'port'     => '3306',
        'username' => 'wyhsaas',
        'password' => '',
        'dbname'   => '',
        'charset'  => 'utf8',
    ],
    'session' => [
        'uniqueId'   => 'wyh-shared',
        'host'       => '127.0.0.1',
        'port'       => 6379,
        'persistent' => false,
        'lifetime'   => 3600 * 12,
        'prefix'     => 'ui_',
        'index'      => 0,
    ],
];
