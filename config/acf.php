<?php

return [

    'settings' => [

        'path' => rtrim(base_path(env('ACF_PATH', 'src/acf')), '/'),
        'url'  => rtrim(dirname(asset_acf()), '/') . '/',

        'json'      => true,
        'save_json' => storage_path(env('ACF_JSON_SAVE', 'app/private/acf/json')),
        'load_json' => array_filter(
            array_map('trim', explode(',', env('ACF_JSON_LOAD', ''))),
            fn ($p) => $p !== ''
        ),

        'capability'       => 'manage_options',
        'show_admin'       => env('APP_DEBUG', false),
        'autoload'         => false,
        'show_updates'     => false,
        'row_index_offset' => 0,
        'local'            => true,
    ],

    'option_pages' => [],

];
