<?php

declare(strict_types=1);

use AshAllenDesign\ConfigValidator\Services\Rule;

return [
    Rule::make('cas_hostname')
        ->rules([
            'required',
            'string',
            'in:sso.gatech.edu,sso-test.gatech.edu',
        ]),

    Rule::make('cas_hostname')
        ->rules([
            'required',
            'string',
            'in:sso.gatech.edu',
        ])
        ->environments([Rule::ENV_PRODUCTION]),

    Rule::make('cas_real_hosts')
        ->rules([
            'required',
            'string',
            'same:cas.cas_hostname',
        ]),

    Rule::make('cas_session_name')
        ->rules([
            'required',
            'string',
            'alpha_dash',
        ]),

    Rule::make('cas_session_name')
        ->rules([
            'required',
            'string',
            'alpha_dash',
            'starts_with:__Host-',
        ])
        ->environments(['test', 'sandbox', Rule::ENV_PRODUCTION]),

    Rule::make('cas_control_session')
        ->rules([
            'required',
            'boolean',
            'declined',
        ]),

    Rule::make('cas_proxy')
        ->rules([
            'required',
            'declined',
        ]),

    Rule::make('cas_port')
        ->rules([
            'required',
            'integer',
            'numeric',
        ]),

    Rule::make('cas_port')
        ->rules([
            'required',
            'integer',
            'numeric',
            'in:443',
        ])
        ->environments(['test', 'sandbox', Rule::ENV_PRODUCTION]),

    Rule::make('cas_uri')
        ->rules([
            'required',
            'string',
        ]),

    Rule::make('cas_uri')
        ->rules([
            'required',
            'string',
            'in:/cas',
        ])
        ->environments(['test', 'sandbox', Rule::ENV_PRODUCTION]),

    Rule::make('cas_client_service')
        ->rules([
            'required',
            'string',
            'same:app.url',
        ]),

    Rule::make('cas_validation')
        ->rules([
            'required',
            'string',
            'in:ca',
        ])
        ->environments(['test', 'sandbox', Rule::ENV_PRODUCTION]),

    Rule::make('cas_cert')
        ->rules([
            'required',
            'string',
        ])
        ->environments(['test', 'sandbox', Rule::ENV_PRODUCTION]),

    Rule::make('cas_validate_cn')
        ->rules([
            'required',
            'boolean',
            'accepted',
        ])
        ->environments(['test', 'sandbox', Rule::ENV_PRODUCTION]),

    Rule::make('cas_login_url')
        ->rules([
            'prohibited',
        ])
        ->environments(['test', 'sandbox', Rule::ENV_PRODUCTION]),

    Rule::make('cas_logout_url')
        ->rules([
            'required',
            'active_url',
        ])
        ->environments(['test', 'sandbox', Rule::ENV_PRODUCTION]),

    Rule::make('cas_logout_redirect')
        ->rules([
            'prohibited',
        ])
        ->environments(['test', 'sandbox', Rule::ENV_PRODUCTION]),

    Rule::make('cas_redirect_path')
        ->rules([
            'prohibited',
        ])
        ->environments(['test', 'sandbox', Rule::ENV_PRODUCTION]),

    Rule::make('cas_enable_saml')
        ->rules([
            'required',
            'boolean',
            'declined',
        ]),

    Rule::make('cas_version')
        ->rules([
            'required',
            'in:3.0',
        ])
        ->environments(['test', 'sandbox', Rule::ENV_PRODUCTION]),

    Rule::make('cas_debug')
        ->rules([
            'required',
            'boolean',
            'declined',
        ])
        ->environments([Rule::ENV_PRODUCTION]),

    Rule::make('cas_verbose_errors')
        ->rules([
            'required',
            'boolean',
            'declined',
        ])
        ->environments([Rule::ENV_PRODUCTION]),

    Rule::make('cas_masquerade')
        ->rules([
            'nullable',
            'prohibited',
        ])
        ->environments(['test', 'sandbox', Rule::ENV_PRODUCTION]),

    Rule::make('cas_session_domain')
        ->rules([
            'nullable',
            'prohibited',
        ])
        ->environments(['test', 'sandbox', Rule::ENV_PRODUCTION]),

    Rule::make('cas_session_secure')
        ->rules([
            'required',
            'boolean',
            'accepted',
        ])
        ->environments(['test', 'sandbox', Rule::ENV_PRODUCTION]),
];
