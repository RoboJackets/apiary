<?php

declare(strict_types=1);

// phpcs:disable SlevomatCodingStandard.ControlStructures.EarlyExit.EarlyExitNotUsed

namespace App\Http;

use Spatie\Csp\Directive;
use Spatie\Csp\Keyword;
use Spatie\Csp\Policies\Policy;
use Spatie\Csp\Value;

class ContentSecurityPolicy extends Policy
{
    public function configure(): void
    {
        if ('' !== config('csp.report_uri') && null !== config('csp.report_uri')) {
            $this->reportTo(
                config('csp.report_uri')
                .'&sentry_environment='.config('app.env')
                .'&sentry_release='.config('sentry.release')
            );
        }
        $this->addDirective(Directive::BASE, config('app.url'));
        $this->addDirective(Directive::BLOCK_ALL_MIXED_CONTENT, Value::NO_VALUE);
        $this->addDirective(Directive::DEFAULT, Keyword::SELF);
        $this->addDirective(Directive::STYLE_ELEM, [
            Keyword::SELF,
            Keyword::UNSAFE_INLINE,
            'https://fonts.googleapis.com',
        ]);
        $this->addDirective(Directive::STYLE, Keyword::UNSAFE_INLINE);
        $this->addDirective(Directive::FONT, 'https://fonts.gstatic.com');
        $this->addDirective(Directive::SCRIPT, [
            Keyword::UNSAFE_EVAL,
        ]);
        $this->addDirective(Directive::SCRIPT_ELEM, [
            Keyword::SELF,
            Keyword::UNSAFE_INLINE,
        ]);
        $this->addDirective(Directive::IMG, [
            Keyword::SELF,
            'data: w3.org/svg/2000',
        ]);
        $this->addDirective(Directive::FRAME_ANCESTORS, Keyword::NONE);
        $this->addDirective(Directive::FRAME, Keyword::NONE);
        $this->addDirective(Directive::CHILD, Keyword::NONE);
        if ('' !== config('sentry.dsn') && null !== config('sentry.dsn')) {
            $this->addDirective(Directive::CONNECT, [
                Keyword::SELF,
                'https://'.parse_url(config('sentry.dsn'), PHP_URL_HOST),
            ]);
        }
    }
}
