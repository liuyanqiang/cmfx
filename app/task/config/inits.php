<?php
return [
    [
        'type'  => 'service',
        'base'  => 'dispatcher',
        'calls' => [
            [
                'type'   => 'method',
                'base'   => 'setDefaultNamespace',
                'params' => [
                    'config://defaults/namespace',
                ],
            ],
            [
                'type'   => 'method',
                'base'   => 'setDefaultTask',
                'params' => [
                    'config://defaults/task',
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
                'base'   => 'setArgument',
                'params' => [
                    'config://argv',
                ],
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
        ],
    ],
];
