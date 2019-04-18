<?php
/**
 * 主配置文件
 */
return [
    /* 启动类 */
    'starter'  => '\Cmfx\Starter\Cli',

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
                'Cmfx'              => CMF_PATH,
                'UI\Tests\Cli'      => TEST_PATH . '/cli',
                'UI\Tests\Tasks'    => TEST_PATH . '/tasks',
                'UI\Tests\Models'   => TEST_PATH . '/models',
                'UI\Tests\Handlers' => TEST_PATH . '/handlers',
                'UI\Tests\Units'    => TEST_PATH . '/units',
                'UI\Models'         => APP_PATH . '/common/models',
                'UI\Extensions'     => APP_PATH . '/common/extensions',
            ],
        ],
    ],

    /* 默认项 (命名空间、控制器、行为) */
    'defaults' => [
        'namespace' => 'UI\Tests\Tasks',
        'task'      => 'help',
        'action'    => 'main',
    ],

    /* 要加载的子项配置 */
    'subkeys'  => ['services', 'inits', 'settings'],

    /* 子项配置所在目录(依次查找) */
    'subdirs'  => [
        TEST_PATH . '/config/' . ENVIRONMENT,
        TEST_PATH . '/config',
        APP_PATH . '/common/config/' . ENVIRONMENT,
        APP_PATH . '/common/config',
    ],
];
