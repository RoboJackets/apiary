<?php

declare(strict_types=1);

// phpcs:disable Generic.Commenting.DocComment.MissingShort
// phpcs:disable Generic.NamingConventions.CamelCapsFunctionName.ScopeNotCamelCaps
// phpcs:disable SlevomatCodingStandard.Functions.DisallowNamedArguments.DisallowedNamedArgument
// phpcs:disable SlevomatCodingStandard.PHP.RequireExplicitAssertion.RequiredExplicitAssertion
// phpcs:disable SlevomatCodingStandard.ControlStructures.RequireTernaryOperator.TernaryOperatorNotUsed

namespace App\Util;

use App\Models\DocuSignEnvelope;
use App\Models\TravelAssignment;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use DocuSign\eSign\Client\ApiClient;
use DocuSign\eSign\Client\ApiException;
use DocuSign\eSign\Client\Auth\OAuth;
use DocuSign\eSign\Client\Auth\UserInfo;
use DocuSign\eSign\Configuration;
use DocuSign\eSign\Model\Checkbox;
use DocuSign\eSign\Model\ConnectEventData;
use DocuSign\eSign\Model\Document;
use DocuSign\eSign\Model\EmailAddress;
use DocuSign\eSign\Model\EmailSettings;
use DocuSign\eSign\Model\EnvelopeDefinition;
use DocuSign\eSign\Model\EventNotification;
use DocuSign\eSign\Model\Expirations;
use DocuSign\eSign\Model\FullName;
use DocuSign\eSign\Model\InitialHere;
use DocuSign\eSign\Model\Notification;
use DocuSign\eSign\Model\PrefillTabs;
use DocuSign\eSign\Model\Radio;
use DocuSign\eSign\Model\RadioGroup;
use DocuSign\eSign\Model\RecipientEmailNotification;
use DocuSign\eSign\Model\Recipients;
use DocuSign\eSign\Model\Reminders;
use DocuSign\eSign\Model\Signer;
use DocuSign\eSign\Model\Tabs;
use DocuSign\eSign\Model\TemplateRole;
use DocuSign\eSign\Model\Text;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Ramsey\Uuid\Uuid;

class DocuSign
{
    private const string CACHE_KEY = 'docusign_access_token';

    private const int TIF_X_ALIGN_TOP = 110;

    private const int TIF_X_ALIGN_BOTTOM = 82;

    private const int TIF_X_ALIGN_FLIGHT_TIME = 228;

    private const int TIF_X_ALIGN_FLIGHT_NUMBER = 332;

    private const int TIF_X_ALIGN_TOTAL_COST = 296;

    private const int TIF_Y_ALIGN_DEPARTURE = 244;

    private const int TIF_Y_ALIGN_RETURN = 275;

    private const int TIF_Y_ALIGN_AIRFARE_REGISTRATION = 328;

    private const int DBA_X_ALIGN_TRAVELER_INFO = 150;

    private const int DBA_X_ALIGN_TRAVELER_TYPE = 56;

    private const int DBA_X_ALIGN_DOMESTIC = 294;

    private const int DBA_X_ALIGN_INTERNATIONAL = 450;

    private const int DBA_Y_ALIGN_NAME = 180;

    private const int DBA_Y_ALIGN_CONTACT = 236;

    private const int DBA_Y_ALIGN_NOTES_FOR_AGENT = 356;

    private const int DBA_Y_ALIGN_NON_EMPLOYEE = 423;

    private const int DBA_Y_ALIGN_EMPLOYEE = 446;

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
     * @return array<string,string|array<array<string,string>>>
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
                            ->setReminderDelay(2 /* days */)
                            ->setReminderFrequency(2 /* days */)
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

    /**
     * Build recipients for travel assignment envelope.
     *
     * @phan-suppress PhanTypeMismatchArgumentProbablyReal
     */
    private static function travelAssignmentRecipients(TravelAssignment $assignment): Recipients
    {
        $fullNameTabs = [];
        $initialHereTabs = [];
        $emailAddressTabs = [];
        $textTabs = [];
        $dateTabs = [];

        if ($assignment->travel->needs_travel_information_form) {
            $fullNameTabs[] = (new FullName())
                ->setTabType('fullName')
                ->setDocumentId(1)
                ->setPageNumber(1)
                ->setFont('CourierNew')
                ->setFontColor('Black')
                ->setFontSize('Size12')
                ->setHeight(20)
                ->setWidth(300)
                ->setXPosition(self::TIF_X_ALIGN_TOP)
                ->setYPosition(131);

            $initialHereTabs[] = (new InitialHere())
                ->setTabType('initialHere')
                ->setDocumentId(1)
                ->setPageNumber(1)
                ->setHeight(200)
                ->setWidth(200)
                ->setScaleValue(1)
                ->setTabOrder(100)
                ->setXPosition(20)
                ->setYPosition(735)
                ->setOptional(false);
        }

        if ($assignment->travel->needs_airfare_form) {
            $initialHereTabs[] = (new InitialHere())
                ->setTabType('initialHere')
                ->setDocumentId(2)
                ->setPageNumber(1)
                ->setHeight(200)
                ->setWidth(200)
                ->setScaleValue(1)
                ->setTabOrder(100)
                ->setXPosition(20)
                ->setYPosition(735)
                ->setOptional(false);

            $initialHereTabs[] = (new InitialHere())
                ->setTabType('initialHere')
                ->setDocumentId(3)
                ->setPageNumber(1)
                ->setHeight(200)
                ->setWidth(200)
                ->setScaleValue(1)
                ->setTabOrder(100)
                ->setXPosition(20)
                ->setYPosition(735)
                ->setOptional(false);

            $emailAddressTabs[] = (new EmailAddress())
                ->setTabType('emailAddress')
                ->setDocumentId(2)
                ->setPageNumber(1)
                ->setFont('CourierNew')
                ->setFontColor('Black')
                ->setFontSize('Size12')
                ->setHeight(20)
                ->setWidth(100)
                ->setXPosition(382)
                ->setYPosition(self::DBA_Y_ALIGN_CONTACT);

            $textTabs[] = (new Text())
                ->setTabType('text')
                ->setDocumentId(2)
                ->setPageNumber(1)
                ->setFont('CourierNew')
                ->setFontColor('Black')
                ->setFontSize('Size8')
                ->setHeight(20)
                ->setWidth(488)
                ->setXPosition(self::DBA_X_ALIGN_TRAVELER_INFO)
                ->setYPosition(self::DBA_Y_ALIGN_NOTES_FOR_AGENT)
                ->setRequired(false)
                ->setTooltip(
                    'If you have a Known Traveler '.
                    'Number, PASS ID, Redress Control Number, or other identifier '.
                    'relevant to air travel, enter it here. Specify what kind of number it is!'
                );
        }

        return (new Recipients())
            ->setSigners([
                (new Signer())
                    ->setRecipientId(Uuid::uuid4()->toString())
                    ->setAddAccessCodeToEmail(false)
                    ->setAgentCanEditEmail(false)
                    ->setAgentCanEditName(false)
                    ->setAllowSystemOverrideForLockedRecipient(false)
                    ->setAutoNavigation(false)
                    ->setCanSignOffline(false)
                    ->setEmail($assignment->user->uid.'@gatech.edu')
                    ->setEmailNotification(
                        (new RecipientEmailNotification())
                            ->setEmailSubject(trim(view(
                                'mail.docusign.travel.subject',
                                ['travel' => $assignment->travel]
                            )->render()))
                            ->setEmailBody(trim(view(
                                'mail.docusign.travel.body',
                                ['travel' => $assignment->travel]
                            )->render()))
                            ->setSupportedLanguage('en')
                    )
                    ->setFirstName($assignment->user->first_name)
                    ->setLastName($assignment->user->last_name)
                    ->setName($assignment->user->full_name)
                    ->setRecipientSuppliesTabs(false)
                    ->setRequireIdLookup(false)
                    ->setRequireSignOnPaper(false)
                    ->setRequireUploadSignature(false)
                    ->setTabs(
                        (new Tabs())
                            ->setFullNameTabs($fullNameTabs)
                            ->setInitialHereTabs($initialHereTabs)
                            ->setEmailAddressTabs($emailAddressTabs)
                            ->setTextTabs($textTabs)
                            ->setDateTabs($dateTabs)
                    ),
            ]);
    }

    /**
     * Build the Document object for the Travel Information Form.
     *
     * @phan-suppress PhanTypeMismatchArgumentProbablyReal
     */
    private static function travelInformationFormDocument(TravelAssignment $assignment): Document
    {
        $departure_date = $assignment->travel->departure_date;
        $return_date = $assignment->travel->return_date;

        $departure_flight_number = null;
        $return_flight_number = null;

        if ($assignment->matrix_itinerary !== null) {
            $departure_date = Matrix::getDepartureDateTime($assignment->matrix_itinerary);
            $departure_flight_number = Matrix::getDepartureFlightNumber($assignment->matrix_itinerary);

            $matrix_return_date = Matrix::getReturnDateTime($assignment->matrix_itinerary);
            $return_flight_number = Matrix::getReturnFlightNumber($assignment->matrix_itinerary);

            if ($matrix_return_date !== null) {
                $return_date = $matrix_return_date;
            }
        }

        return (new Document())
            ->setDocumentId(1)
            ->setDisplay('inline')
            ->setDocumentBase64(base64_encode(file_get_contents(resource_path('pdf/travel_information_form.pdf'))))
            ->setIncludeInDownload(true)
            ->setName('Travel Information Form')
            ->setTabs(
                (new Tabs())
                    ->setPrefillTabs(
                        (new PrefillTabs())
                            ->setTextTabs([
                                (new Text())
                                    ->setTabType('text')
                                    ->setDocumentId(1)
                                    ->setPageNumber(1)
                                    ->setFont('CourierNew')
                                    ->setFontColor('Black')
                                    ->setFontSize('Size12')
                                    ->setHeight(20)
                                    ->setWidth(530)
                                    ->setXPosition(self::TIF_X_ALIGN_TOP)
                                    ->setYPosition(170)
                                    ->setValue($assignment->travel->destination),
                                (new Text())
                                    ->setTabType('text')
                                    ->setDocumentId(1)
                                    ->setPageNumber(1)
                                    ->setFont('CourierNew')
                                    ->setFontColor('Black')
                                    ->setFontSize('Size12')
                                    ->setHeight(20)
                                    ->setWidth(530)
                                    ->setXPosition(self::TIF_X_ALIGN_TOP)
                                    ->setYPosition(202)
                                    ->setValue($assignment->travel->tar_purpose),
                                (new Text())
                                    ->setTabType('text')
                                    ->setDocumentId(1)
                                    ->setPageNumber(1)
                                    ->setFont('CourierNew')
                                    ->setFontColor('Black')
                                    ->setFontSize('Size12')
                                    ->setHeight(20)
                                    ->setWidth(100)
                                    ->setXPosition(self::TIF_X_ALIGN_BOTTOM)
                                    ->setYPosition(self::TIF_Y_ALIGN_DEPARTURE)
                                    ->setValue($departure_date->format('Y-m-d')),
                                ...($departure_flight_number !== null ? [
                                    (new Text())
                                        ->setTabType('text')
                                        ->setDocumentId(1)
                                        ->setPageNumber(1)
                                        ->setFont('CourierNew')
                                        ->setFontColor('Black')
                                        ->setFontSize('Size12')
                                        ->setHeight(20)
                                        ->setWidth(80)
                                        ->setXPosition(self::TIF_X_ALIGN_FLIGHT_TIME)
                                        ->setYPosition(self::TIF_Y_ALIGN_DEPARTURE)
                                        ->setValue($departure_date->format('h:ia')),
                                    (new Text())
                                        ->setTabType('text')
                                        ->setDocumentId(1)
                                        ->setPageNumber(1)
                                        ->setFont('CourierNew')
                                        ->setFontColor('Black')
                                        ->setFontSize('Size12')
                                        ->setHeight(20)
                                        ->setWidth(280)
                                        ->setXPosition(self::TIF_X_ALIGN_FLIGHT_NUMBER)
                                        ->setYPosition(self::TIF_Y_ALIGN_DEPARTURE)
                                        ->setValue($departure_flight_number),
                                ] : []),
                                ...($assignment->matrix_itinerary !== null && $departure_flight_number === null ? [
                                    (new Text())
                                        ->setTabType('text')
                                        ->setDocumentId(1)
                                        ->setPageNumber(1)
                                        ->setFont('CourierNew')
                                        ->setFontColor('Black')
                                        ->setFontSize('Size12')
                                        ->setHeight(20)
                                        ->setWidth(280)
                                        ->setXPosition(self::TIF_X_ALIGN_FLIGHT_NUMBER)
                                        ->setYPosition(self::TIF_Y_ALIGN_DEPARTURE)
                                        ->setValue('See attached itinerary'),
                                ] : []),
                                (new Text())
                                    ->setTabType('text')
                                    ->setDocumentId(1)
                                    ->setPageNumber(1)
                                    ->setFont('CourierNew')
                                    ->setFontColor('Black')
                                    ->setFontSize('Size12')
                                    ->setHeight(20)
                                    ->setWidth(100)
                                    ->setXPosition(self::TIF_X_ALIGN_BOTTOM)
                                    ->setYPosition(self::TIF_Y_ALIGN_RETURN)
                                    ->setValue($return_date->format('Y-m-d')),
                                ...($return_flight_number !== null ? [
                                    (new Text())
                                        ->setTabType('text')
                                        ->setDocumentId(1)
                                        ->setPageNumber(1)
                                        ->setFont('CourierNew')
                                        ->setFontColor('Black')
                                        ->setFontSize('Size12')
                                        ->setHeight(20)
                                        ->setWidth(80)
                                        ->setXPosition(self::TIF_X_ALIGN_FLIGHT_TIME)
                                        ->setYPosition(self::TIF_Y_ALIGN_RETURN)
                                        ->setValue($return_date->format('h:ia')),
                                    (new Text())
                                        ->setTabType('text')
                                        ->setDocumentId(1)
                                        ->setPageNumber(1)
                                        ->setFont('CourierNew')
                                        ->setFontColor('Black')
                                        ->setFontSize('Size12')
                                        ->setHeight(20)
                                        ->setWidth(280)
                                        ->setXPosition(self::TIF_X_ALIGN_FLIGHT_NUMBER)
                                        ->setYPosition(self::TIF_Y_ALIGN_RETURN)
                                        ->setValue($return_flight_number),
                                ] : []),
                                ...(
                                    $assignment->matrix_itinerary !== null &&
                                    Matrix::getSliceCount($assignment->matrix_itinerary) > 1 &&
                                    $return_flight_number === null ?
                                    [
                                        (new Text())
                                            ->setTabType('text')
                                            ->setDocumentId(1)
                                            ->setPageNumber(1)
                                            ->setFont('CourierNew')
                                            ->setFontColor('Black')
                                            ->setFontSize('Size12')
                                            ->setHeight(20)
                                            ->setWidth(280)
                                            ->setXPosition(self::TIF_X_ALIGN_FLIGHT_NUMBER)
                                            ->setYPosition(self::TIF_Y_ALIGN_RETURN)
                                            ->setValue('See attached itinerary'),
                                    ] : []),
                                (new Text())
                                    ->setTabType('text')
                                    ->setDocumentId(1)
                                    ->setPageNumber(1)
                                    ->setFont('CourierNew')
                                    ->setFontColor('Black')
                                    ->setFontSize('Size12')
                                    ->setHeight(20)
                                    ->setWidth(100)
                                    ->setXPosition(self::TIF_X_ALIGN_BOTTOM)
                                    ->setYPosition(300)
                                    ->setValue('$'.($assignment->travel->meal_per_diem ?? 0)),
                                (new Text())
                                    ->setTabType('text')
                                    ->setDocumentId(1)
                                    ->setPageNumber(1)
                                    ->setFont('CourierNew')
                                    ->setFontColor('Black')
                                    ->setFontSize('Size12')
                                    ->setHeight(20)
                                    ->setWidth(128)
                                    ->setXPosition(self::TIF_X_ALIGN_BOTTOM)
                                    ->setYPosition(self::TIF_Y_ALIGN_AIRFARE_REGISTRATION)
                                    ->setValue(
                                        '$'.(
                                            $assignment->matrix_itinerary === null ?
                                                0 :
                                                intval(ceil(
                                                    Matrix::getHighestDisplayPrice($assignment->matrix_itinerary) ?? 0
                                                ))
                                        )
                                    ),
                                ...(
                                    $assignment->travel->hotel_name === null ||
                                    $assignment->travel->tar_lodging === null ||
                                    $assignment->travel->tar_lodging === 0 ? [] : [
                                        (new Text())
                                            ->setTabType('text')
                                            ->setDocumentId(1)
                                            ->setPageNumber(1)
                                            ->setFont('CourierNew')
                                            ->setFontColor('Black')
                                            ->setFontSize('Size12')
                                            ->setHeight(20)
                                            ->setWidth(247)
                                            ->setXPosition(self::TIF_X_ALIGN_BOTTOM)
                                            ->setYPosition(359)
                                            ->setValue($assignment->travel->hotel_name),
                                    ]),
                                (new Text())
                                    ->setTabType('text')
                                    ->setDocumentId(1)
                                    ->setPageNumber(1)
                                    ->setFont('CourierNew')
                                    ->setFontColor('Black')
                                    ->setFontSize('Size12')
                                    ->setHeight(20)
                                    ->setWidth(129)
                                    ->setXPosition(self::TIF_X_ALIGN_BOTTOM)
                                    ->setYPosition(390)
                                    ->setValue('$'.($assignment->travel->tar_lodging ?? 0)),
                                (new Text())
                                    ->setTabType('text')
                                    ->setDocumentId(1)
                                    ->setPageNumber(1)
                                    ->setFont('CourierNew')
                                    ->setFontColor('Black')
                                    ->setFontSize('Size12')
                                    ->setHeight(20)
                                    ->setWidth(129)
                                    ->setXPosition(self::TIF_X_ALIGN_BOTTOM)
                                    ->setYPosition(432)
                                    ->setValue('$'.($assignment->travel->car_rental_cost ?? 0)),
                                (new Text())
                                    ->setTabType('text')
                                    ->setDocumentId(1)
                                    ->setPageNumber(1)
                                    ->setFont('CourierNew')
                                    ->setFontColor('Black')
                                    ->setFontSize('Size12')
                                    ->setHeight(20)
                                    ->setWidth(350)
                                    ->setXPosition(268)
                                    ->setYPosition(self::TIF_Y_ALIGN_AIRFARE_REGISTRATION)
                                    ->setValue('$'.($assignment->travel->tar_registration ?? 0)),
                                (new Text())
                                    ->setTabType('text')
                                    ->setDocumentId(1)
                                    ->setPageNumber(1)
                                    ->setFont('CourierNew')
                                    ->setFontColor('Black')
                                    ->setFontSize('Size12')
                                    ->setHeight(20)
                                    ->setWidth(240)
                                    ->setXPosition(self::TIF_X_ALIGN_BOTTOM)
                                    ->setYPosition(497)
                                    ->setValue($assignment->travel->tar_project_number),
                                (new Text())
                                    ->setTabType('text')
                                    ->setDocumentId(1)
                                    ->setPageNumber(1)
                                    ->setFont('CourierNew')
                                    ->setFontColor('Black')
                                    ->setFontSize('Size12')
                                    ->setHeight(20)
                                    ->setWidth(200)
                                    ->setXPosition(142)
                                    ->setYPosition(539)
                                    ->setValue($assignment->user->emergency_contact_phone),
                            ])
                            ->setRadioGroupTabs([
                                ...(
                                    $assignment->travel->tar_lodging !== null && $assignment->travel->tar_lodging > 0 ?
                                        [
                                            (new RadioGroup())
                                                ->setTabType('radiogroup')
                                                ->setGroupName('hotel_amount_total')
                                                ->setDocumentId(1)
                                                ->setRadios([
                                                    (new Radio())
                                                        ->setPageNumber(1)
                                                        ->setValue('total')
                                                        ->setXPosition(self::TIF_X_ALIGN_TOTAL_COST)
                                                        ->setYPosition(397)
                                                        ->setRequired(true)
                                                        ->setSelected(true),
                                                ]),
                                        ] : []
                                ),
                                ...(
                                    $assignment->travel->car_rental_cost !== null &&
                                    $assignment->travel->car_rental_cost > 0 ?
                                        [
                                            (new RadioGroup())
                                                ->setTabType('radiogroup')
                                                ->setGroupName('car_rental_amount_total')
                                                ->setDocumentId(1)
                                                ->setRadios([
                                                    (new Radio())
                                                        ->setPageNumber(1)
                                                        ->setValue('total')
                                                        ->setXPosition(self::TIF_X_ALIGN_TOTAL_COST)
                                                        ->setYPosition(439)
                                                        ->setRequired(true)
                                                        ->setSelected(true),
                                                ]),
                                        ] : []
                                ),
                            ])
                    )
            );
    }

    /**
     * Build the Document object for the Single Trip Direct Bill Airfare Request Form.
     *
     * @phan-suppress PhanTypeMismatchArgumentProbablyReal
     */
    private static function directBillAirfareFormDocument(TravelAssignment $assignment): Document
    {
        $departure_date = Matrix::getDepartureDateTime($assignment->matrix_itinerary);
        $return_date = $assignment->travel->return_date;

        $matrix_return_date = Matrix::getReturnDateTime($assignment->matrix_itinerary);

        if ($matrix_return_date !== null) {
            $return_date = $matrix_return_date;
        }

        $checkboxTabs = [];

        if ($assignment->user->employee_id === null) {
            $checkboxYAlign = self::DBA_Y_ALIGN_NON_EMPLOYEE;
        } else {
            $checkboxYAlign = self::DBA_Y_ALIGN_EMPLOYEE;
        }

        $checkboxTabs[] = (new Checkbox())
            ->setTabType('checkbox')
            ->setDocumentId(2)
            ->setPageNumber(1)
            ->setXPosition(self::DBA_X_ALIGN_TRAVELER_TYPE)
            ->setYPosition($checkboxYAlign)
            ->setSelected(true);

        if (Matrix::hasStopOutsideUnitedStates($assignment->matrix_itinerary)) {
            $checkboxTabs[] = (new Checkbox())
                ->setTabType('checkbox')
                ->setDocumentId(2)
                ->setPageNumber(1)
                ->setXPosition(self::DBA_X_ALIGN_INTERNATIONAL)
                ->setYPosition($checkboxYAlign)
                ->setSelected(true);
        } else {
            $checkboxTabs[] = (new Checkbox())
                ->setTabType('checkbox')
                ->setDocumentId(2)
                ->setPageNumber(1)
                ->setXPosition(self::DBA_X_ALIGN_DOMESTIC)
                ->setYPosition($checkboxYAlign)
                ->setSelected(true);
        }

        $textTabs = [
            (new Text())
                ->setTabType('text')
                ->setDocumentId(2)
                ->setPageNumber(1)
                ->setFont('CourierNew')
                ->setFontColor('Black')
                ->setFontSize('Size12')
                ->setHeight(20)
                ->setWidth(35)
                ->setXPosition(53)
                ->setYPosition(89)
                ->setValue($assignment->travel->department_number),
            (new Text())
                ->setTabType('text')
                ->setDocumentId(2)
                ->setPageNumber(1)
                ->setFont('CourierNew')
                ->setFontColor('Black')
                ->setFontSize('Size12')
                ->setHeight(20)
                ->setWidth(150)
                ->setXPosition(150)
                ->setYPosition(self::DBA_Y_ALIGN_NAME)
                ->setValue($assignment->user->last_name),
            (new Text())
                ->setTabType('text')
                ->setDocumentId(2)
                ->setPageNumber(1)
                ->setFont('CourierNew')
                ->setFontColor('Black')
                ->setFontSize('Size12')
                ->setHeight(20)
                ->setWidth(100)
                ->setXPosition(377)
                ->setYPosition(self::DBA_Y_ALIGN_NAME)
                ->setValue($assignment->user->first_name),
            (new Text())
                ->setTabType('text')
                ->setDocumentId(2)
                ->setPageNumber(1)
                ->setFont('CourierNew')
                ->setFontColor('Black')
                ->setFontSize('Size12')
                ->setHeight(20)
                ->setWidth(100)
                ->setXPosition(148)
                ->setYPosition(self::DBA_Y_ALIGN_CONTACT)
                ->setValue($assignment->user->phone),
            (new Text())
                ->setTabType('text')
                ->setDocumentId(2)
                ->setPageNumber(1)
                ->setFont('CourierNew')
                ->setFontColor('Black')
                ->setFontSize('Size12')
                ->setHeight(20)
                ->setWidth(100)
                ->setXPosition(181)
                ->setYPosition(263)
                ->setValue($assignment->travel->tar_purpose),
            (new Text())
                ->setTabType('text')
                ->setDocumentId(2)
                ->setPageNumber(1)
                ->setFont('CourierNew')
                ->setFontColor('Black')
                ->setFontSize('Size9')
                ->setHeight(20)
                ->setWidth(488)
                ->setXPosition(self::DBA_X_ALIGN_TRAVELER_INFO)
                ->setYPosition(305)
                ->setValue(Matrix::getOriginDestinationString($assignment->matrix_itinerary)),
            (new Text())
                ->setTabType('text')
                ->setDocumentId(2)
                ->setPageNumber(1)
                ->setFont('CourierNew')
                ->setFontColor('Black')
                ->setFontSize('Size9')
                ->setHeight(20)
                ->setWidth(200)
                ->setXPosition(self::DBA_X_ALIGN_TRAVELER_INFO)
                ->setYPosition(326)
                ->setValue($departure_date->format('Y-m-d').' - '.$return_date->format('Y-m-d')),
            (new Text())
                ->setTabType('text')
                ->setDocumentId(2)
                ->setPageNumber(1)
                ->setFont('CourierNew')
                ->setFontColor('Black')
                ->setFontSize('Size12')
                ->setHeight(20)
                ->setWidth(163)
                ->setXPosition(150)
                ->setYPosition(386)
                ->setValue($assignment->travel->tar_project_number),
        ];

        if ($assignment->user->employee_id !== null) {
            $textTabs[] = (new Text())
                ->setTabType('text')
                ->setDocumentId(2)
                ->setPageNumber(1)
                ->setFont('CourierNew')
                ->setFontColor('Black')
                ->setFontSize('Size12')
                ->setHeight(20)
                ->setWidth(165)
                ->setXPosition(149)
                ->setYPosition(465)
                ->setValue($assignment->user->employee_id);
        }

        if ($assignment->user->legal_middle_name !== null) {
            $textTabs[] = (new Text())
                ->setTabType('text')
                ->setDocumentId(2)
                ->setPageNumber(1)
                ->setFont('CourierNew')
                ->setFontColor('Black')
                ->setFontSize('Size12')
                ->setHeight(20)
                ->setWidth(100)
                ->setXPosition(492)
                ->setYPosition(self::DBA_Y_ALIGN_NAME)
                ->setValue($assignment->user->legal_middle_name);
        }

        return (new Document())
            ->setDocumentId(2)
            ->setDisplay('inline')
            ->setDocumentBase64(base64_encode(file_get_contents(resource_path('pdf/direct_bill_airfare_form.pdf'))))
            ->setIncludeInDownload(true)
            ->setName('Request for Single-Trip Direct Billing of Airfare')
            ->setTabs(
                (new Tabs())
                    ->setPrefillTabs(
                        (new PrefillTabs())
                            ->setTextTabs($textTabs)
                            ->setCheckboxTabs($checkboxTabs)
                    )
            );
    }

    /**
     * Build an airfare request document. This shows the entire detailed itinerary in a human-readable format.
     *
     * @phan-suppress PhanTypeMismatchArgumentProbablyReal
     */
    private static function airfareRequestDocument(TravelAssignment $assignment): Document
    {
        return (new Document())
            ->setDocumentId(3)
            ->setDisplay('inline')
            ->setDocumentBase64(
                base64_encode(
                    Pdf::loadView('travel.matrixitineraryprint', ['assignment' => $assignment])->output()
                )
            )
            ->setIncludeInDownload(true)
            ->setName('Itinerary Request');
    }

    /**
     * Builds the array of documents for this travel assignment.
     *
     * @return array<\DocuSign\eSign\Model\Document>
     */
    private static function travelAssignmentDocuments(TravelAssignment $assignment): array
    {
        $documents = [];

        if ($assignment->travel->needs_travel_information_form) {
            $documents[] = self::travelInformationFormDocument($assignment);
        }

        if ($assignment->travel->needs_airfare_form) {
            $documents[] = self::directBillAirfareFormDocument($assignment);
            $documents[] = self::airfareRequestDocument($assignment);
        }

        return $documents;
    }

    private static function travelAssignmentEmailSettings(TravelAssignment $assignment): EmailSettings
    {
        return (new EmailSettings())
            ->setReplyEmailAddressOverride($assignment->travel->primaryContact->gt_email)
            ->setReplyEmailNameOverride($assignment->travel->primaryContact->full_name);
    }

    /**
     * Build the EnvelopeDefinition for a travel assignment.
     *
     * @phan-suppress PhanTypeMismatchArgumentProbablyReal
     */
    public static function travelAssignmentEnvelopeDefinition(DocuSignEnvelope $envelope): EnvelopeDefinition
    {
        return (new EnvelopeDefinition())
            ->setStatus('sent')
            ->setRecipients(self::travelAssignmentRecipients($envelope->signable))
            ->setRecipientsLock(true)
            ->setDocuments(self::travelAssignmentDocuments($envelope->signable))
            ->setEmailSubject(
                trim(view(
                    'mail.docusign.travel.subject',
                    ['travel' => $envelope->signable->travel]
                )->render()).' for '.$envelope->signedBy->full_name
            )
            ->setEmailBlurb(null)
            ->setMessageLock(true)
            ->setEmailSettings(self::travelAssignmentEmailSettings($envelope->signable))
            ->setNotification(
                (new Notification())
                    ->setUseAccountDefaults(false)
                    ->setReminders(
                        (new Reminders())
                            ->setReminderEnabled(true)
                            ->setReminderDelay(1 /* days */)
                            ->setReminderFrequency(1 /* days */)
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
            ->setAllowViewHistory(false)
            ->setAutoNavigation(false)
            ->setEnableWetSign(false)
            ->setEnvelopeIdStamping(true)
            ->setEventNotifications(self::eventNotifications($envelope))
            ->setUseDisclosure(true)
            ->setSignerCanSignOnMobile(true)
            ->setSigningLocation('online');
    }
}
