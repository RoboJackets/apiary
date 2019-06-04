<?php declare(strict_types = 1);

return [
    'target_php_version' => '7.2',

    'directory_list' => [
        '.',
    ],

    'exclude_analysis_directory_list' => [
        'vendor/',
        'database/',
    ],

    'suppress_issue_types' => [
        'PhanInvalidFQSENInCallable',
        'PhanPartialTypeMismatchArgument',
        'PhanPartialTypeMismatchArgumentInternal',
        'PhanPartialTypeMismatchReturn',
        'PhanPluginMixedKeyNoKey',
        'PhanPluginNonBoolInLogicalArith',
        'PhanPossiblyFalseTypeArgumentInternal',
        'PhanUndeclaredClassMethod',
        'PhanUndeclaredFunction',
        'PhanUndeclaredFunctionInCallable',
        'PhanUndeclaredMethod',
        'PhanUnreferencedClass',
        'PhanUnreferencedClosure',
        'PhanUnreferencedProtectedProperty',
        'PhanUnreferencedPublicMethod',
        'PhanUnreferencedPublicProperty',
        'PhanUnusedClosureParameter',
        'PhanUnusedPublicMethodParameter',
        'PhanUnusedPublicNoOverrideMethodParameter',
        'PhanUnusedVariableValueOfForeachWithKey',
        'PhanWriteOnlyProtectedProperty',
        'PhanWriteOnlyPublicProperty',
    ],

    'allow_missing_properties' => true,
    'backward_compatibility_checks' => false,
    'enable_include_path_checks' => true,
    'strict_method_checking' => true,
    'strict_param_checking' => true,
    'strict_return_checking' => true,
    'dead_code_detection' => true,

    'plugins' => [
        'DollarDollarPlugin',
        'AlwaysReturnPlugin',
        'DuplicateArrayKeyPlugin',
        'PregRegexCheckerPlugin',
        'PrintfCheckerPlugin',
        'UnreachableCodePlugin',
        'NonBoolBranchPlugin',
        'NonBoolInLogicalArithPlugin',
        'DuplicateExpressionPlugin',
        'UnusedSuppressionPlugin',
    ],
];
