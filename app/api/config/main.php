<?php
/**
 * 主配置文件
 */
return [
    /* 启动类 */
    'starter'  => '\Cmfx\Starter\Mvc',

    /**
     * 加载器(Loader)的注册设置
     */
    'register' => [
        [
            'method' => 'registerFiles',
            'params' => [
                APP_PATH . '/common/helpers/functions.php',
            ],
        ],
        [
            'method' => 'registerNamespaces',
            'params' => [
                'Cmfx'           => CMF_PATH,
                'UI\Controllers' => APP_PATH . '/api/controllers',
                'UI\Handlers'    => APP_PATH . '/api/handlers',
                'UI\Models'      => APP_PATH . '/common/models',
                'UI\Extensions'  => APP_PATH . '/common/extensions',
            ],
        ],
    ],

    /* 默认项 (命名空间、控制器、行为) */
    'defaults' => [
        'namespace'  => 'UI\Controllers',
        'controller' => 'default',
        'action'     => 'index',
    ],

    /* 指定目录 (视图) */
    'dirs'     => [
        'views' => APP_PATH . '/'. PROJECT_NAME .'/views/',
    ],

    /* 要加载的子项配置 */
    'subkeys'  => ['services', 'inits', 'settings', 'params'],

    /* 子项配置所在目录(依次查找) */
    'subdirs'  => [
        APP_PATH . '/' . PROJECT_NAME . '/config/' . ENVIRONMENT,
        APP_PATH . '/' . PROJECT_NAME . '/config',
        APP_PATH . '/common/config/' . ENVIRONMENT,
        APP_PATH . '/common/config',
    ],
];
