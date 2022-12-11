<?php

use AshAllenDesign\ConfigValidator\Services\Rule;

return [
    Rule::make('postmark.token')
        ->rules([
            'required',
            'string',
            'alpha_dash'
        ])
        ->environments(['test', Rule::ENV_PRODUCTION]),

    Rule::make('github.client_id')
        ->rules([
            'required',
            'string'
        ])
        ->environments([Rule::ENV_PRODUCTION]),

    Rule::make('github.client_secret')
        ->rules([
            'required',
            'string'
        ])
        ->environments([Rule::ENV_PRODUCTION]),

    Rule::make('github.redirect')
        ->rules([
            'required',
            'string'
        ])
        ->environments([Rule::ENV_PRODUCTION]),

    Rule::make('google.client_id')
        ->rules([
            'required',
            'string'
        ])
        ->environments([Rule::ENV_PRODUCTION]),

    Rule::make('google.client_secret')
        ->rules([
            'required',
            'string'
        ])
        ->environments([Rule::ENV_PRODUCTION]),

    Rule::make('google.redirect')
        ->rules([
            'required',
            'string'
        ])
        ->environments([Rule::ENV_PRODUCTION]),

    Rule::make('team_slack_webhook_url')
        ->rules([
            'required',
            'string',
            'active_url',
        ])
        ->environments([Rule::ENV_PRODUCTION]),

    Rule::make('treasurer_slack_webhook_url')
        ->rules([
            'required',
            'string',
            'active_url',
        ])
        ->environments([Rule::ENV_PRODUCTION]),

    Rule::make('treasurer_email')
        ->rules([
            'required',
            'string',
            'email:rfc,strict,dns,spoof',
        ])
        ->environments([Rule::ENV_PRODUCTION]),

    Rule::make('core_slack_webhook_url')
        ->rules([
            'required',
            'string',
            'active_url',
        ])
        ->environments([Rule::ENV_PRODUCTION]),

    Rule::make('core_officers_slack_webhook_url')
        ->rules([
            'required',
            'string',
            'active_url',
        ])
        ->environments([Rule::ENV_PRODUCTION]),

    Rule::make('membership_agreement_archive_email')
        ->rules([
            'required_if:features.docusign-membership-agreement,true',
            'string',
            'email:rfc,strict,dns,spoof',
        ])
        ->environments([Rule::ENV_PRODUCTION]),
];
