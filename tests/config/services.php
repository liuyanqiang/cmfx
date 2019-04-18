<?php
return [
    'redis'         => [
        'definition' => [
            'type'   => 'class',
            'base'   => '\UI\Extensions\Redis',
            'params' => [
                'config://settings/redis',
            ],
        ],
        'shared'     => true,
    ],
    'db'            => [
        'definition' => [
            'type'   => 'class',
            'base'   => '\Phalcon\Db\Adapter\Pdo\Mysql',
            'params' => [
                'config://settings/db',
            ],
        ],
        'shared'     => true,
    ],
    'mongo'         => [
        'definition' => [
            'type'   => 'class',
            'base'   => '\Cmfx\Mvc\Collection\Manager',
            'params' => [
                'config://settings/mongo/connection',
            ],
            'calls'  => [
                [
                    'type'   => 'method',
                    'base'   => 'selectDatabase',
                    'params' => [
                        'config://settings/mongo/db',
                    ],
                ],
            ],
        ],
        'shared'     => true,
    ],
    'application'   => [
        'definition' => '\UI\Tests\Cli\Console',
        'shared'     => true,
    ],
    'eventsManager' => [
        'definition' => '\Phalcon\Events\Manager',
        'shared'     => true,
    ],
];
