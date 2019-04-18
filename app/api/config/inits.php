<?php
/* 服务必要的初始化 */
return [
    [
        'type'  => 'service',
        'base'  => 'router',
        'calls' => [
            [
                'type'   => 'method',
                'base'   => 'setDefaultNamespace',
                'params' => [
                    'config://defaults/namespace',
                ],
            ],
        ],
    ],
    [
        'type'  => 'service',
        'base'  => 'dispatcher',
        'calls' => [
            [
                'type'   => 'method',
                'base'   => 'setDefaultController',
                'params' => [
                    'config://defaults/controller',
                ],
            ],
            [
                'type'   => 'method',
                'base'   => 'setDefaultAction',
                'params' => [
                    'config://defaults/action',
                ],
            ],
            [
                'type'   => 'method',
                'base'   => 'setEventsManager',
                'params' => [
                    'service://eventsManager',
                ],
            ],
        ],
    ],
    [
        'type'  => 'service',
        'base'  => 'application',
        'calls' => [
            [
                'type'   => 'method',
                'base'   => 'setEventsManager',
                'params' => [
                    'service://eventsManager',
                ],
            ],
            [
                'type'   => 'method',
                'base'   => 'useImplicitView',
                'params' => [false],
            ],
        ],
    ],
    [
        'type'  => 'service',
        'base'  => 'eventsManager',
        'calls' => [
            [
                'type'   => 'method',
                'base'   => 'attach',
                'params' => [
                    'dispatch:beforeException',
                    [
                        'type' => 'class',
                        'base' => '\UI\Handlers\Dispatcher',
                    ],
                ],
            ],
            [
                'type'   => 'method',
                'base'   => 'attach',
                'params' => [
                    'application',
                    [
                        'type' => 'class',
                        'base' => '\UI\Handlers\Application',
                    ],
                ],
            ],
        ],
    ],
];
