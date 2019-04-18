<?php
/* 服务定义 */
return [
    'request'           => [
        'definition' => '\Cmfx\Http\Request',
        'shared'     => true,
    ],
    'cache'             => [
        'definition' => [
            'type'   => 'class',
            'base'   => '\Cmfx\Cache\Adapter\Redis',
            'params' => [
                'config://settings/cache',
            ],
        ],
        'shared'     => true,
    ],
    'db'                => [
        'definition' => [
            'type'   => 'class',
            'base'   => '\Phalcon\Db\Adapter\Pdo\Mysql',
            'params' => [
                'config://settings/db',
            ],
        ],
        'shared'     => true,
    ],
    'view'              => [
        'definition' => [
            'type'  => 'class',
            'base'  => '\Phalcon\Mvc\View',
            'calls' => [
                [
                    'type'   => 'method',
                    'base'   => 'setViewsDir',
                    'params' => [
                        'config://dirs/views',
                    ],
                ],
            ],
        ],
        'shared'     => false,
    ],
    'session'           => [
        'definition' => [
            'type'   => 'class',
            'base'   => '\Phalcon\Session\Adapter\Redis',
            'params' => [
                'config://settings/session',
            ],
//            'calls'  => [
//                [
//                    'type' => 'method',
//                    'base' => 'start',
//                ],
//            ],
        ],
        'shared'     => true,
    ],
    'syslog'            => [
        'definition' => [
            'type'   => 'class',
            'base'   => '\Cmfx\Logger',
            'params' => [
                [
                    'type' => 'class',
                    'base' => '\UI\Models\Syslog',
                ],
            ],
        ],
        'shared'     => true,
    ],
    'assets'            => [
        'definition' => [
            'type'  => 'class',
            'base'  => '\Cmfx\Assets\Manager',
            'calls' => [
                [
                    'type'   => 'method',
                    'base'   => 'setPublicPath',
                    'params' => [
                        PUBLIC_PATH,
                    ],
                ],
                [
                    'type'   => 'method',
                    'base'   => 'setPackage',
                    'params' => [
                        (ENVIRONMENT === 'production'),
                    ],
                ],
            ],
        ],
        'shared'     => true,
    ],
    'application'       => [
        'definition' => '\Phalcon\Mvc\Application',
        'shared'     => true,
    ],
    'eventsManager'     => [
        'definition' => '\Phalcon\Events\Manager',
        'shared'     => true,
    ],
];
