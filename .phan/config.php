<?php

declare(strict_types=1);

return [
    'target_php_version' => '7.2',

    'directory_list' => [
        '.',
    ],

    'exclude_analysis_directory_list' => [
        'bootstrap/cache/',
        'database/',
        'public/',
        'resources/',
        'storage/',
        'vendor/',
    ],

    'exclude_file_list' => [
        '.phpstorm.meta.php',
        '_ide_helper.php',
    ],

    'suppress_issue_types' => [
        'PhanInvalidFQSENInCallable',
        'PhanPartialTypeMismatchArgument',
        'PhanPartialTypeMismatchArgumentInternal',
        'PhanPartialTypeMismatchReturn',
        'PhanPluginMixedKeyNoKey',
        'PhanPluginNonBoolInLogicalArith',
        'PhanPossiblyFalseTypeArgumentInternal',
        'PhanPossiblyNullTypeArgument',
        'PhanReadOnlyPHPDocProperty',
        'PhanReadOnlyProtectedProperty',
        'PhanUndeclaredClassMethod',
        'PhanUndeclaredFunction',
        'PhanUndeclaredFunctionInCallable',
        'PhanUndeclaredMethod',
        'PhanUnreferencedClass',
        'PhanUnreferencedClosure',
        'PhanUnreferencedPHPDocProperty',
        'PhanUnreferencedProtectedProperty',
        'PhanUnreferencedPublicMethod',
        'PhanUnreferencedPublicProperty',
        'PhanUnusedClosureParameter',
        'PhanUnusedPublicMethodParameter',
        'PhanUnusedPublicNoOverrideMethodParameter',
        'PhanUnusedVariableValueOfForeachWithKey',
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
