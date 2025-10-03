<?php

return [
    'default' => 'default',
    'documentations' => [
        'default' => [
            'api' => [
                'title' => 'Message Sending System API',
            ],
            'routes' => [
                /*
                 * Route for accessing parsed swagger annotations.
                 */
                'docs' => 'api/documentation',
            ],
            'paths' => [
                /*
                 * Edit to include full URL in UI for assets
                 */
                'use_absolute_path' => env('L5_SWAGGER_USE_ABSOLUTE_PATH', true),

                /*
                 * File name of the generated json documentation file
                 */
                'docs_json' => 'api-docs.json',

                /*
                 * File name of the generated YAML documentation file
                 */
                'docs_yaml' => 'api-docs.yaml',

                /*
                 * Set this to `json` or `yaml` to determine which documentation file to use in UI
                 */
                'format_to_use_for_docs' => env('L5_FORMAT_TO_USE_FOR_DOCS', 'json'),

                /*
                 * Absolute paths to directory containing the swagger annotations are stored.
                 */
                'annotations' => [
                    base_path('app'),
                ],

                /*
                 * Absolute path to directory where to export documentation
                 */
                'docs' => base_path('storage/api-docs'),

                /*
                 * Absolute path to directories that should be exclude from scanning
                 */
                'excludes' => [
                    base_path('tests'),
                ],

                /*
                 * Absolute path to the base directory
                 */
                'base' => base_path(),
            ],
        ],
    ],
    'defaults' => [
        'routes' => [
            /*
             * Route for accessing parsed swagger annotations.
             */
            'docs' => 'docs',

            /*
             * Route for Oauth2 authentication callback.
             */
            'oauth2_callback' => 'api/oauth2-callback',

            /*
             * Middleware allows to prevent unexpected access to API documentation
             */
            'middleware' => [
                'api' => [],
                'asset' => [],
                'docs' => [],
                'oauth2_callback' => [],
            ],

            /*
             * Route Group options
             */
            'group_options' => [],
        ],

        'paths' => [
            /*
             * Edit to include full URL in UI for assets
             */
            'use_absolute_path' => env('L5_SWAGGER_USE_ABSOLUTE_PATH', true),

            /*
             * File name of the generated json documentation file
             */
            'docs_json' => 'api-docs.json',

            /*
             * File name of the generated YAML documentation file
             */
            'docs_yaml' => 'api-docs.yaml',

            /*
             * Set this to `json` or `yaml` to determine which documentation file to use in UI
             */
            'format_to_use_for_docs' => env('L5_FORMAT_TO_USE_FOR_DOCS', 'json'),

            /*
             * Absolute paths to directory containing the swagger annotations are stored.
             */
            'annotations' => [
                base_path('app'),
            ],

            /*
             * Absolute path to directory where to export documentation
             */
            'docs' => base_path('storage/api-docs'),

            /*
             * Absolute path to directories that should be exclude from scanning
             */
            'excludes' => [
                base_path('tests'),
            ],

            /*
             * Absolute path to the base directory
             */
            'base' => base_path(),
        ],

        'scanOptions' => [
            /**
             * analyser: defaults to \OpenApi\StaticAnalyser .
             */
            'analyser' => null,

            /**
             * analysis: defaults to a new \OpenApi\Analysis .
             */
            'analysis' => null,

            /**
             * Custom query path processors classes.
             */
            'processors' => [
                // new \App\SwaggerProcessors\SchemaQueryParameter(),
            ],

            /**
             * pattern: string       $pattern File pattern(s) to scan (default: *.php) .
             */
            'pattern' => '*.php',

            /*
             * Absolute path to directories that should be exclude from scanning
             * @note This option overwrites `$exclude` property in `OpenApi\scan()`.
             */
            'exclude' => [
                // 'tests',
                // 'storage',
            ],

            /*
             * Validators
             */
            'validate' => true,

            /*
             * Additional files or directories to scan
             */
            'additional' => [
                // base_path('app'),
            ],
        ],

        'securityDefinitions' => [
            'securitySchemes' => [
                /*
                 * Examples of Security schemes
                 */
                'BearerAuth' => [
                    'type' => 'http',
                    'scheme' => 'bearer',
                    'bearerFormat' => 'JWT',
                ],
            ],
            'security' => [
                /*
                 * Examples of Securities
                 */
                [
                    'BearerAuth' => []
                ],
            ],
        ],

        'generate_always' => env('L5_SWAGGER_GENERATE_ALWAYS', false),
        'generate_yaml_copy' => env('L5_SWAGGER_GENERATE_YAML_COPY', false),
        'proxy' => false,
        'additional_config_url' => null,
        'operations_sort' => env('L5_SWAGGER_OPERATIONS_SORT', null),
        'validator_url' => null,
        'ui' => [
            'display' => [
                /*
                 * Controls the default expansion setting for the operations and tags. It can be :
                 * 'list' (expands only the tags),
                 * 'full' (expands the tags and operations),
                 * 'none' (expands nothing).
                 */
                'default_models_expand_depth' => 1,
                'default_model_expand_depth' => 1,
                'default_model_rendering' => 'example',
                'display_operation_id' => false,
                'display_request_duration' => false,
                'doc_expansion' => 'none',
                'filter' => false,
                'max_displayed_tags' => null,
                'show_extensions' => false,
                'show_common_extensions' => false,
            ],

            'authorization' => [
                'persist_authorization' => false,
            ],
        ],
        'constants' => [
            'L5_SWAGGER_CONST_HOST' => env('L5_SWAGGER_CONST_HOST', 'http://localhost'),
        ],
    ],
];
