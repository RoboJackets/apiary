<?php

use AshAllenDesign\ConfigValidator\Services\Rule;

return [
    Rule::make('redis.client')
        ->rules([
            'required',
            'string',
            'in:phpredis',
        ])
        ->environments(['test', Rule::ENV_PRODUCTION]),

    Rule::make('redis.default.host')
        ->rules([
            'required',
            'string',
            'file',
        ])
        ->environments(['test', Rule::ENV_PRODUCTION]),

    Rule::make('redis.default.password')
        ->rules([
            'required',
            'string',
        ])
        ->environments(['test', Rule::ENV_PRODUCTION]),

    Rule::make('redis.default.port')
        ->rules([
            'required',
            'integer',
            'numeric',
            'in:-1',
        ])
        ->environments(['test', Rule::ENV_PRODUCTION]),

    Rule::make('redis.default.scheme')
        ->rules([
            'prohibited'
        ])
        ->environments(['test', Rule::ENV_PRODUCTION]),

    Rule::make('redis.default.database')
        ->rules([
            'required',
            'integer',
            'numeric',
        ])
        ->environments(['test', Rule::ENV_PRODUCTION]),

    Rule::make('redis.cache.host')
        ->rules([
            'required',
            'string',
            'file',
        ])
        ->environments(['test', Rule::ENV_PRODUCTION]),

    Rule::make('redis.cache.password')
        ->rules([
            'required',
            'string',
        ])
        ->environments(['test', Rule::ENV_PRODUCTION]),

    Rule::make('redis.cache.port')
        ->rules([
            'required',
            'integer',
            'numeric',
            'in:-1',
        ])
        ->environments(['test', Rule::ENV_PRODUCTION]),

    Rule::make('redis.cache.scheme')
        ->rules([
            'prohibited'
        ])
        ->environments(['test', Rule::ENV_PRODUCTION]),

    Rule::make('redis.cache.database')
        ->rules([
            'required',
            'integer',
            'numeric',
            'different:database.redis.default.database',
        ])
        ->environments(['test', Rule::ENV_PRODUCTION]),
];
