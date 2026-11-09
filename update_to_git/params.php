<?php

if (!defined('NGCMS')) {
    exit('HAL');
}

return [
    'sources' => [
        'core' => [
            'user' => 'irbees2008',
            'repository' => 'ngcms-core',
            'branch' => 'master',
            'base_ref' => engineVersionBuild,
            'destination_prefix' => '',
            'except' => [
                '.htaccess',
                'robots.txt',
                'engine/upgrade_090-092.php',
            ],
        ],
        'plugins' => [
            'user' => 'irbees2008',
            'repository' => 'ngcms-plugins',
            'branch' => 'master',
            'base_ref' => '',
            'destination_prefix' => 'engine/plugins',
            'except' => [
                'README.md',
            ],
        ],
    ],
];
