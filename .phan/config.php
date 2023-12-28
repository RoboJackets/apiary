<?php

declare(strict_types=1);

return [
    'target_php_version' => '8.2',

    'directory_list' => [
        '.',
    ],

    'exclude_analysis_directory_list' => [
        'bootstrap/cache/',
        'database/',
        'public/',
        'resources/',
        'storage/',
        'tests/',
        'vendor/',
    ],

    'exclude_file_list' => [
        '.phpstorm.meta.php',
        '_ide_helper.php',
        'stubs/Permission.php',
        'stubs/Role.php',
        'vendor/inertiajs/inertia-laravel/_ide_helpers.php',
    ],

    'suppress_issue_types' => [
        'PhanAbstractStaticMethodCall',
        'PhanAccessNonStaticToStatic',
        'PhanCompatibleStandaloneType',
        'PhanInvalidFQSENInCallable',
        'PhanPartialTypeMismatchArgument',
        'PhanPartialTypeMismatchArgumentInternal',
        'PhanPartialTypeMismatchReturn',
        'PhanPluginMixedKeyNoKey',
        'PhanPluginNonBoolInLogicalArith',
        'PhanPossiblyFalseTypeArgumentInternal',
        'PhanPossiblyNonClassMethodCall',
        'PhanPossiblyNullTypeArgument',
        'PhanPossiblyUndeclaredMethod',
        'PhanReadOnlyPHPDocProperty',
        'PhanReadOnlyProtectedProperty',
        'PhanStaticCallToNonStatic',
        'PhanTypeInvalidCallableArrayKey',
        'PhanTypeMismatchArgumentSuperType',
        'PhanUndeclaredFunctionInCallable',
        'PhanUndeclaredMethod',
        'PhanUnreferencedClosure',
        'PhanUnreferencedPHPDocProperty',
        'PhanUnreferencedProtectedMethod',
        'PhanUnreferencedProtectedProperty',
        'PhanUnreferencedPublicMethod',
        'PhanUnreferencedPublicProperty',
        'PhanUnusedClosureParameter',
        'PhanUnusedPublicMethodParameter',
        'PhanUnusedPublicNoOverrideMethodParameter',
        'PhanWriteOnlyPHPDocProperty',
        'PhanWriteOnlyProtectedProperty',
        'PhanWriteOnlyPublicProperty',
    ],

    'allow_missing_properties' => true,
    'backward_compatibility_checks' => false,
    'dead_code_detection' => true,
    'enable_extended_internal_return_type_plugins' => true,
    'enable_include_path_checks' => true,
    'strict_method_checking' => true,
    'strict_param_checking' => true,
    'strict_return_checking' => true,

    'plugins' => [
        'AlwaysReturnPlugin',
        'DollarDollarPlugin',
        'DuplicateArrayKeyPlugin',
        'DuplicateExpressionPlugin',
        'NonBoolBranchPlugin',
        'NonBoolInLogicalArithPlugin',
        'PregRegexCheckerPlugin',
        'PrintfCheckerPlugin',
        'UnreachableCodePlugin',
        'UnusedSuppressionPlugin',
    ],
];
