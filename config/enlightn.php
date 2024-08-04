<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Enlightn Analyzer Classes
    |--------------------------------------------------------------------------
    |
    | The following array lists the "analyzer" classes that will be registered
    | with Enlightn. These analyzers run an analysis on the application via
    | various methods such as static analysis. Feel free to customize it.
    |
    */
    'analyzers' => ['*'],

    // If you wish to skip running some analyzers, list the classes in the array below.
    'exclude_analyzers' => [
        \Enlightn\Enlightn\Analyzers\Performance\DebugLogAnalyzer::class,
        \Enlightn\Enlightn\Analyzers\Performance\MinificationAnalyzer::class,
        \Enlightn\Enlightn\Analyzers\Reliability\CustomErrorPageAnalyzer::class,
        \Enlightn\Enlightn\Analyzers\Reliability\EnvFileAnalyzer::class,
        \Enlightn\Enlightn\Analyzers\Reliability\ForeachIterableAnalyzer::class,
        \Enlightn\Enlightn\Analyzers\Reliability\InvalidMethodCallAnalyzer::class,
        \Enlightn\Enlightn\Analyzers\Reliability\InvalidPropertyAccessAnalyzer::class,
        \Enlightn\Enlightn\Analyzers\Reliability\InvalidReturnTypeAnalyzer::class,
        \Enlightn\Enlightn\Analyzers\Security\UpToDateDependencyAnalyzer::class,
        \Enlightn\Enlightn\Analyzers\Security\XSSAnalyzer::class,
        ...(env('SKIP_PHPSTAN_CHECKS', false) === true ? [
            \Enlightn\Enlightn\Analyzers\Performance\CollectionCallAnalyzer::class,
            \Enlightn\Enlightn\Analyzers\Reliability\DeadCodeAnalyzer::class,
            \Enlightn\Enlightn\Analyzers\Reliability\DeprecatedCodeAnalyzer::class,
            \Enlightn\Enlightn\Analyzers\Reliability\ForeachIterableAnalyzer::class,
            \Enlightn\Enlightn\Analyzers\Reliability\InvalidFunctionCallAnalyzer::class,
            \Enlightn\Enlightn\Analyzers\Reliability\InvalidImportAnalyzer::class,
            \Enlightn\Enlightn\Analyzers\Reliability\InvalidMethodCallAnalyzer::class,
            \Enlightn\Enlightn\Analyzers\Reliability\InvalidMethodOverrideAnalyzer::class,
            \Enlightn\Enlightn\Analyzers\Reliability\InvalidOffsetAnalyzer::class,
            \Enlightn\Enlightn\Analyzers\Reliability\InvalidPropertyAccessAnalyzer::class,
            \Enlightn\Enlightn\Analyzers\Reliability\InvalidReturnTypeAnalyzer::class,
            \Enlightn\Enlightn\Analyzers\Reliability\MissingModelRelationAnalyzer::class,
            \Enlightn\Enlightn\Analyzers\Reliability\MissingReturnStatementAnalyzer::class,
            \Enlightn\Enlightn\Analyzers\Reliability\UndefinedConstantAnalyzer::class,
            \Enlightn\Enlightn\Analyzers\Reliability\UndefinedVariableAnalyzer::class,
            \Enlightn\Enlightn\Analyzers\Reliability\UnsetAnalyzer::class,
            \Enlightn\Enlightn\Analyzers\Security\FillableForeignKeyAnalyzer::class,
            \Enlightn\Enlightn\Analyzers\Security\MassAssignmentAnalyzer::class,
        ] : []),
        ...(env('APP_ENV', 'local') === 'test' ? [] : [
            \Enlightn\Enlightn\Analyzers\Security\AppDebugAnalyzer::class,
        ]),
    ],

    // If you wish to skip running some analyzers in CI mode, list the classes below.
    'ci_mode_exclude_analyzers' => [
        \Enlightn\Enlightn\Analyzers\Performance\QueueDriverAnalyzer::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Enlightn Analyzer Paths
    |--------------------------------------------------------------------------
    |
    | The following array lists the "analyzer" paths that will be searched
    | recursively to find analyzer classes. This option will only be used
    | if the analyzers option above is set to the asterisk wildcard. The
    | key is the base namespace to resolve the class name.
    |
    */
    'analyzer_paths' => [
        'Enlightn\\Enlightn\\Analyzers' => base_path('vendor/enlightn/enlightn/src/Analyzers'),
        'Enlightn\\EnlightnPro\\Analyzers' => base_path('vendor/enlightn/enlightnpro/src/Analyzers'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Enlightn Base Path
    |--------------------------------------------------------------------------
    |
    | The following array lists the directories that will be scanned for
    | application specific code. By default, we are scanning your app
    | folder, migrations folder and the seeders folder.
    |
    */
    'base_path' => [
        app_path(),
        database_path('migrations'),
        database_path('seeders'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Environment Specific Analyzers
    |--------------------------------------------------------------------------
    |
    | There are some analyzers that are meant to be run for specific environments.
    | The options below specify whether we should skip environment specific
    | analyzers if the environment does not match.
    |
    */
    'skip_env_specific' => env('ENLIGHTN_SKIP_ENVIRONMENT_SPECIFIC', false),

    /*
    |--------------------------------------------------------------------------
    | Guest URL
    |--------------------------------------------------------------------------
    |
    | Specify any guest url or path (preferably your app's login url) here. This
    | would be used by Enlightn to inspect your application HTTP headers.
    | Example: '/login'.
    |
    */
    'guest_url' => '/privacy',

    /*
    |--------------------------------------------------------------------------
    | Exclusions From Reporting
    |--------------------------------------------------------------------------
    |
    | Specify the analyzer classes that you wish to exclude from reporting. This
    | means that if any of these analyzers fail, they will not be counted
    | towards the exit status of the Enlightn command. This is useful
    | if you wish to run the command in your CI/CD pipeline.
    | Example: [\Enlightn\Enlightn\Analyzers\Security\XSSAnalyzer::class].
    |
    */
    'dont_report' => [
        ...(env('SKIP_HTTP_CHECKS', false) === true ? [
            \Enlightn\Enlightn\Analyzers\Performance\CacheHeaderAnalyzer::class,
            \Enlightn\Enlightn\Analyzers\Security\HSTSHeaderAnalyzer::class,
        ] : []),
        \Enlightn\Enlightn\Analyzers\Security\StableDependencyAnalyzer::class,
        \Enlightn\Enlightn\Analyzers\Security\VulnerableDependencyAnalyzer::class,
        \Enlightn\Enlightn\Analyzers\Performance\UnusedGlobalMiddlewareAnalyzer::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Ignoring Errors
    |--------------------------------------------------------------------------
    |
    | Use this config option to ignore specific errors. The key of this array
    | would be the analyzer class and the value would be an associative
    | array with path and details. Run php artisan enlightn:baseline
    | to auto-generate this. Patterns are supported in details.
    |
    */
    'ignore_errors' => [
        \Enlightn\Enlightn\Analyzers\Security\FillableForeignKeyAnalyzer::class => [
            [
                'path' => 'app/Models/Merchandise.php',
                'details' => 'Potential foreign key fiscal_year_id declared as fillable and available for mass assignment.',
            ],
            [
                'path' => 'app/Models/Signature.php',
                'details' => 'Potential foreign key membership_agreement_template_id declared as fillable and available for mass assignment.',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Analyzer Configurations
    |--------------------------------------------------------------------------
    |
    | The following configuration options pertain to individual analyzers.
    | These are recommended options but feel free to customize them based
    | on your application needs.
    |
    */
    'license_whitelist' => [
        'Apache-2.0', 'Apache2', 'BSD-2-Clause', 'BSD-3-Clause', 'LGPL-2.1-only', 'LGPL-2.1',
        'LGPL-2.1-or-later', 'LGPL-3.0', 'LGPL-3.0-only', 'LGPL-3.0-or-later', 'MIT', 'ISC',
        'CC0-1.0', 'Unlicense', 'WTFPL', 'OSL-3.0',
    ],

    /*
    |--------------------------------------------------------------------------
    | Credentials
    |--------------------------------------------------------------------------
    |
    | The following credentials are used to share your Enlightn report with
    | the Enlightn Github Bot. This allows the bot to compile the report
    | and add review comments on your pull requests.
    |
    */
    'credentials' => [
        'username' => env('ENLIGHTN_USERNAME'),
        'api_token' => env('ENLIGHTN_API_TOKEN'),
    ],

    // Set this value to your Github repo for integrating with the Enlightn Github Bot
    // Format: "myorg/myrepo" like "laravel/framework".
    'github_repo' => env('ENLIGHTN_GITHUB_REPO'),

    // Set to true to restrict the max number of files displayed in the enlightn
    // command for each check. Set to false to display all files.
    'compact_lines' => false,

    // List your commercial packages (licensed by you) below, so that they are not
    // flagged by the License Analyzer.
    'commercial_packages' => [
        'apimatic/core',
        'apimatic/core-interfaces',
        'enlightn/enlightnpro',
        'phpmyadmin/sql-parser',
        'mwgg/airports',
    ],

    'allowed_permissions' => [
        base_path() => '775',
        app_path() => '775',
        resource_path() => '775',
        storage_path() => '775',
        public_path() => '775',
        config_path() => '775',
        database_path() => '775',
        base_path('routes') => '775',
        app()->bootstrapPath() => '775',
        app()->bootstrapPath('cache') => '775',
        app()->bootstrapPath('app.php') => '664',
        base_path('artisan') => '775',
        public_path('index.php') => '664',
        public_path('server.php') => '664',
    ],

    'writable_directories' => [
        storage_path(),
        app()->bootstrapPath('cache'),
    ],
];
