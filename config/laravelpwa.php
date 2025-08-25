<?php

return [
    'name' => config('laravelpwa.name'),
    'manifest' => [
        'name' => config('laravelpwa.manifest.name'),
        'short_name' => config('laravelpwa.manifest.short_name'),
        'start_url' => '/',
        'background_color' => config('laravelpwa.manifest.background_color'),
        'theme_color' => config('laravelpwa.manifest.theme_color'),
        'display' => 'standalone',
        'orientation'=> 'any',
        'status_bar'=> 'black',
        'icons' => [
            '72x72' => [
                'path' => config('laravelpwa.manifest.icons.72x72.path'),
                'purpose' => 'any'
            ],
            '96x96' => [
                'path' => config('laravelpwa.manifest.icons.96x96.path'),
                'purpose' => 'any'
            ],
            '128x128' => [
                'path' => config('laravelpwa.manifest.icons.128x128.path'),
                'purpose' => 'any'
            ],
            '144x144' => [
                'path' => config('laravelpwa.manifest.icons.144x144.path'),
                'purpose' => 'any'
            ],
            '152x152' => [
                'path' => config('laravelpwa.manifest.icons.152x152.path'),
                'purpose' => 'any'
            ],
            '192x192' => [
                'path' => config('laravelpwa.manifest.icons.192x192.path'),
                'purpose' => 'any'
            ],
            '384x384' => [
                'path' => config('laravelpwa.manifest.icons.384x384.path'),
                'purpose' => 'any'
            ],
            '512x512' => [
                'path' => config('laravelpwa.manifest.icons.512x512.path'),
                'purpose' => 'any'
            ],
        ],
        'splash' => [
            '640x1136' => config('laravelpwa.manifest.splash.640x1136'),
            '750x1334' => config('laravelpwa.manifest.splash.750x1334'),
            '828x1792' => config('laravelpwa.manifest.splash.828x1792'),
            '1125x2436' => config('laravelpwa.manifest.splash.1125x2436'),
            '1242x2208' => config('laravelpwa.manifest.splash.1242x2208'),
            '1242x2688' => config('laravelpwa.manifest.splash.1242x2688'),
            '1536x2048' => config('laravelpwa.manifest.splash.1536x2048'),
            '1668x2224' => config('laravelpwa.manifest.splash.1668x2224'),
            '1668x2388' => config('laravelpwa.manifest.splash.1668x2388'),
            '2048x2732' => config('laravelpwa.manifest.splash.2048x2732'),
        ],
        /*'shortcuts' => [
            [
                'name' => 'Shortcut Link 1',
                'description' => 'Shortcut Link 1 Description',
                'url' => 'shortcutlink1',
                'icons' => [
                    'src' => 'images/icons/icon-72x72.png',
                    'purpose' => 'any'
                ]
            ],
            [
                'name' => 'Shortcut Link 2',
                'description' => 'Shortcut Link 2 Description',
                'url' => 'shortcutlink2'
            ]
        ],*/
        'custom' => []
    ]
];
