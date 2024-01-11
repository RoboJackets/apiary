<?php

declare(strict_types=1);

// phpcs:disable Generic.Commenting.DocComment.MissingShort
// phpcs:disable Generic.NamingConventions.CamelCapsFunctionName.ScopeNotCamelCaps
// phpcs:disable SlevomatCodingStandard.Functions.DisallowNamedArguments.DisallowedNamedArgument
// phpcs:disable SlevomatCodingStandard.PHP.RequireExplicitAssertion.RequiredExplicitAssertion
// phpcs:disable SlevomatCodingStandard.ControlStructures.RequireTernaryOperator.TernaryOperatorNotUsed

namespace App\Util;

use App\Models\DocuSignEnvelope;
use App\Models\User;
use Carbon\Carbon;
use DocuSign\eSign\Client\ApiClient;
use DocuSign\eSign\Client\ApiException;
use DocuSign\eSign\Client\Auth\OAuth;
use DocuSign\eSign\Client\Auth\UserInfo;
use DocuSign\eSign\Configuration;
use DocuSign\eSign\Model\ConnectEventData;
use DocuSign\eSign\Model\EmailSettings;
use DocuSign\eSign\Model\EnvelopeDefinition;
use DocuSign\eSign\Model\EventNotification;
use DocuSign\eSign\Model\Expirations;
use DocuSign\eSign\Model\Notification;
use DocuSign\eSign\Model\RecipientEmailNotification;
use DocuSign\eSign\Model\Reminders;
use DocuSign\eSign\Model\TemplateRole;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;

class DocuSign
{
    private const CACHE_KEY = 'docusign_access_token';

    private static function getConfiguration(bool $withAccessToken): Configuration
    {
        $config = new Configuration([
            'curlTimeout' => config('docusign.read_timeout'),
            'curlConnectTimeout' => config('docusign.connect_timeout'),
            'debug' => config('app.debug'),
            'debugFile' => 'php://stderr',
            'host' => config('docusign.api_base_path'),
        ]);

        if ($withAccessToken) {
            $config->setAccessToken(self::getAccessToken());
        }

        return $config;
    }

    private static function getOAuth(): OAuth
    {
        $oauth = new OAuth();
        $oauth->setBasePath(config('docusign.api_base_path'));

        return $oauth;
    }

    public static function getApiClient(bool $withAccessToken = true): ApiClient
    {
        return new ApiClient(config: self::getConfiguration($withAccessToken), oAuth: self::getOAuth());
    }

    public static function getApiClientForUser(User $user): ?ApiClient
    {
        $config = self::getConfiguration(false);

        if ($user->docusign_access_token === null) {
            return null;
        }

        if ($user->docusign_access_token_expires_at < Carbon::now()) {
            if ($user->docusign_refresh_token === null) {
                return null;
            }

            if ($user->docusign_refresh_token_expires_at < Carbon::now()) {
                return null;
            }

            try {
                /** @var \DocuSign\eSign\Client\Auth\OAuthToken $tokens */
                $tokens = self::getApiClient(withAccessToken: false)->refreshAccessToken(
                    client_id: config('docusign.client_id'),
                    client_secret: config('docusign.client_secret'),
                    refresh_token: $user->docusign_refresh_token
                )[0];
            } catch (ApiException) {
                return null;
            }

            $user->docusign_access_token = $tokens->getAccessToken();
            $user->docusign_access_token_expires_at = Carbon::now()->addSeconds($tokens->getExpiresIn() - 60);
            $user->docusign_refresh_token = $tokens->getRefreshToken();
            $user->save();
        }

        $config->setAccessToken($user->docusign_access_token);

        $apiClient = new ApiClient(config: $config, oAuth: self::getOAuth());

        try {
            $apiClient->getUserInfo($user->docusign_access_token);
        } catch (ApiException) {
            return null;
        }

        return $apiClient;
    }

    /**
     * Serialize a DocuSign UserInfo object to an array. You'd think this would work out of the box, but no.
     *
     * @return array<string,string|array<int,string>>
     */
    public static function serializeUserInfo(UserInfo $userInfo): array
    {
        $accounts = [];

        /** @var \DocuSign\eSign\Client\Auth\Account $account */
        foreach ($userInfo->getAccounts() as $account) {
            /** @var \DocuSign\eSign\Client\Auth\Organization $organization */
            $organization = $account->getOrganization();

            $accounts[] = [
                'account_id' => $account->getAccountId(),
                'account_name' => $account->getAccountName(),
                'base_uri' => $account->getBaseUri(),
                'organization_id' => $organization->getOrganizationId(),
            ];
        }

        return [
            'sub' => $userInfo->getSub(),
            'name' => $userInfo->getName(),
            'given_name' => $userInfo->getGivenName(),
            'family_name' => $userInfo->getFamilyName(),
            'email' => $userInfo->getEmail(),
            'accounts' => $accounts,
        ];
    }

    public static function getState(): string
    {
        return bin2hex(openssl_random_pseudo_bytes(128));
    }

    private static function getAccessToken(): string
    {
        $access_token = Cache::get(self::CACHE_KEY);

        if ($access_token === null) {
            $docusign = self::getApiClient(withAccessToken: false);

            /** @var \DocuSign\eSign\Client\Auth\OAuthToken $tokens */
            $tokens = $docusign->requestJWTUserToken(
                client_id: config('docusign.client_id'),
                user_id: config('docusign.impersonate_user_id'),
                rsa_private_key: config('docusign.private_key')
            )[0];

            Cache::put(
                key: self::CACHE_KEY,
                value: $tokens->getAccessToken(),
                ttl: $tokens->getExpiresIn() - 60
            );

            return $tokens->getAccessToken();
        } else {
            return $access_token;
        }
    }

    private static function membershipAgreementTemplateRoleForMember(User $user): TemplateRole
    {
        return (new TemplateRole())
            ->setEmail($user->uid.'@gatech.edu')
            ->setName($user->full_name)
            ->setRoleName('Member')
            ->setEmailNotification(
                (new RecipientEmailNotification())
                    ->setEmailSubject(trim(view('mail.docusign.agreement.member.subject')->render()))
                    ->setEmailBody(trim(view('mail.docusign.agreement.member.body')->render()))
                    ->setSupportedLanguage('en')
            );
    }

    private static function membershipAgreementTemplateRoleForParentOrGuardian(User $user): TemplateRole
    {
        return (new TemplateRole())
            ->setEmail($user->parent_guardian_email)
            ->setName($user->parent_guardian_name)
            ->setRoleName('Parent or Guardian')
            ->setEmailNotification(
                (new RecipientEmailNotification())
                    ->setEmailSubject(
                        trim(view('mail.docusign.agreement.parent.subject', ['user' => $user])->render())
                    )
                    ->setEmailBody(
                        trim(view('mail.docusign.agreement.parent.body', ['user' => $user])->render())
                    )
                    ->setSupportedLanguage('en')
            );
    }

    /**
     * Build the EventNotifications objects needed to get events back from DocuSign.
     *
     * @return array<\DocuSign\eSign\Model\EventNotification>
     *
     * @phan-suppress PhanTypeMismatchArgumentProbablyReal
     */
    private static function eventNotifications(DocuSignEnvelope $envelope): array
    {
        return [
            (new EventNotification())
                ->setEventData(
                    (new ConnectEventData())
                        ->setVersion('restv2.1')
                        ->setIncludeData(
                            [
                                'recipients',
                            ]
                        )
                )
                ->setDeliveryMode('SIM')
                ->setEvents(
                    [
                        'envelope-created',
                        'envelope-sent',
                        'envelope-resent',
                        'envelope-delivered',
                        'envelope-completed',
                        'envelope-declined',
                        'envelope-voided',
                        'recipient-authenticationfailed',
                        'recipient-autoresponded',
                        'recipient-declined',
                        'recipient-delivered',
                        'recipient-completed',
                        'recipient-sent',
                        'recipient-resent',
                        'template-created',
                        'template-modified',
                        'template-deleted',
                        'envelope-corrected',
                        'envelope-purge',
                        'envelope-deleted',
                        'envelope-discard',
                        'recipient-reassign',
                        'recipient-delegate',
                        'recipient-finish-later',
                        'click-agreed',
                        'click-declined',
                    ]
                )
                ->setIncludeEnvelopeVoidReason(false)
                ->setLoggingEnabled(true)
                ->setRequireAcknowledgment(true)
                ->setUrl(
                    URL::signedRoute('webhook-client-docusign', ['internalEnvelopeId' => $envelope->id])
                ),
            (new EventNotification())
                ->setEventData(
                    (new ConnectEventData())
                        ->setVersion('restv2.1')
                        ->setIncludeData(
                            [
                                'recipients',
                                'documents',
                            ]
                        )
                )
                ->setDeliveryMode('SIM')
                ->setEvents(
                    [
                        'envelope-completed',
                    ]
                )
                ->setIncludeEnvelopeVoidReason(false)
                ->setLoggingEnabled(true)
                ->setRequireAcknowledgment(true)
                ->setUrl(
                    URL::signedRoute('webhook-client-docusign', ['internalEnvelopeId' => $envelope->id])
                ),
        ];
    }

    /**
     * Build an EnvelopeDefinition for a membership agreement where only the member needs to sign.
     *
     * @phan-suppress PhanTypeMismatchArgumentProbablyReal
     */
    private static function membershipAgreementEnvelopeDefinitionForMemberOnly(
        DocuSignEnvelope $envelope
    ): EnvelopeDefinition {
        return (new EnvelopeDefinition())
            ->setStatus('sent')
            ->setTemplateId(config('docusign.templates.membership_agreement_member_only'))
            ->setTemplateRoles(
                [
                    self::membershipAgreementTemplateRoleForMember($envelope->signedBy),
                ]
            )
            ->setEmailSubject(
                trim(view('mail.docusign.agreement.parent.subject', ['user' => $envelope->signedBy])->render())
            )
            ->setEmailBlurb(trim(view('mail.docusign.agreement.member.body')->render()))
            ->setEmailSettings(
                (new EmailSettings())
                    ->setReplyEmailAddressOverride(config('docusign.service_account_reply_to.address'))
                    ->setReplyEmailNameOverride(config('docusign.service_account_reply_to.name'))
            )->setNotification(
                (new Notification())
                    ->setUseAccountDefaults(false)
                    ->setReminders(
                        (new Reminders())
                            ->setReminderEnabled(true)
                            ->setReminderDelay(2)
                            ->setReminderFrequency(2)
                    )
                    ->setExpirations(
                        (new Expirations())
                            ->setExpireEnabled(true)
                            ->setExpireWarn(10)
                            ->setExpireAfter(60)
                    )
            )
            ->setAllowComments(false)
            ->setAllowMarkup(false)
            ->setAllowReassign(false)
            ->setAllowRecipientRecursion(false)
            ->setAllowViewHistory(true)
            ->setAutoNavigation(false)
            ->setEnableWetSign(true)
            ->setEnvelopeIdStamping(true)
            ->setEventNotifications(self::eventNotifications($envelope))
            ->setUseDisclosure(true);
    }

    /**
     * Build an EnvelopeDefinition for a membership agreement where the member and their parent or guardian both need
     * to sign.
     */
    private static function membershipAgreementEnvelopeDefinitionForMemberAndParentOrGuardian(
        DocuSignEnvelope $envelope
    ): EnvelopeDefinition {
        return self::membershipAgreementEnvelopeDefinitionForMemberOnly($envelope)
            ->setTemplateId(config('docusign.templates.membership_agreement_member_and_guardian'))
            ->setTemplateRoles(
                [
                    self::membershipAgreementTemplateRoleForMember($envelope->signedBy),
                    self::membershipAgreementTemplateRoleForParentOrGuardian($envelope->signedBy),
                ]
            );
    }

    public static function membershipAgreementEnvelopeDefinition(DocuSignEnvelope $envelope): EnvelopeDefinition
    {
        if ($envelope->signedBy->needs_parent_or_guardian_signature) {
            return self::membershipAgreementEnvelopeDefinitionForMemberAndParentOrGuardian($envelope);
        } else {
            return self::membershipAgreementEnvelopeDefinitionForMemberOnly($envelope);
        }
    }
}
