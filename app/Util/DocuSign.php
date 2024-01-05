<?php

declare(strict_types=1);

// phpcs:disable Generic.Commenting.DocComment.MissingShort
// phpcs:disable Generic.NamingConventions.CamelCapsFunctionName.ScopeNotCamelCaps
// phpcs:disable SlevomatCodingStandard.Functions.DisallowNamedArguments.DisallowedNamedArgument
// phpcs:disable SlevomatCodingStandard.PHP.RequireExplicitAssertion.RequiredExplicitAssertion
// phpcs:disable SlevomatCodingStandard.ControlStructures.RequireTernaryOperator.TernaryOperatorNotUsed

namespace App\Util;

use App\Models\DocuSignEnvelope;
use App\Models\Travel;
use App\Models\TravelAssignment;
use App\Models\User;
use Carbon\Carbon;
use DocuSign\eSign\Client\ApiClient;
use DocuSign\eSign\Client\ApiException;
use DocuSign\eSign\Client\Auth\OAuth;
use DocuSign\eSign\Client\Auth\UserInfo;
use DocuSign\eSign\Configuration;
use DocuSign\eSign\Model\ConnectEventData;
use DocuSign\eSign\Model\Document;
use DocuSign\eSign\Model\EmailAddress;
use DocuSign\eSign\Model\EmailSettings;
use DocuSign\eSign\Model\EnvelopeDefinition;
use DocuSign\eSign\Model\EventNotification;
use DocuSign\eSign\Model\Expirations;
use DocuSign\eSign\Model\FirstName;
use DocuSign\eSign\Model\FullName;
use DocuSign\eSign\Model\InitialHere;
use DocuSign\eSign\Model\LastName;
use DocuSign\eSign\Model\Notification;
use DocuSign\eSign\Model\PrefillTabs;
use DocuSign\eSign\Model\RecipientEmailNotification;
use DocuSign\eSign\Model\Recipients;
use DocuSign\eSign\Model\Reminders;
use DocuSign\eSign\Model\Signer;
use DocuSign\eSign\Model\Tabs;
use DocuSign\eSign\Model\TemplateRole;
use DocuSign\eSign\Model\Text;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Ramsey\Uuid\Uuid;

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

    private static function emailSubject(Travel $travel): string
    {
        if ($travel->tar_required && $travel->needs_airfare_form) {
            return $travel->name.' Travel Forms';
        } elseif ($travel->tar_required && ! $travel->needs_airfare_form) {
            return $travel->name.' Travel Information Form';
        } elseif (! $travel->tar_required && $travel->needs_airfare_form) {
            return $travel->name.' Airfare Request Form';
        }

        throw new Exception('Unexpected trip configuration');
    }

    /**
     * Build recipients for travel assignment envelope.
     *
     * @phan-suppress PhanPluginNonBoolBranch
     * @phan-suppress PhanTypeMismatchArgumentProbablyReal
     */
    private static function travelAssignmentRecipients(TravelAssignment $assignment): Recipients
    {
        $emailBody = 'Please carefully review and initial the included form as soon as possible. Georgia Tech requires'.
            ' this form for all official travel.';

        if ($assignment->travel->needs_airfare_form && $assignment->travel->tar_required) {
            $emailBody = 'Please carefully review and complete the included forms as soon as possible so we can book '.
                'airfare for you. Georgia Tech requires these forms for all official travel.';
        }

        if ($assignment->travel->needs_airfare_form && ! $assignment->travel->tar_required) {
            $emailBody = 'Please carefully review and complete the included form as soon as possible so we can book '.
                'airfare for you. Georgia Tech requires this form for all official travel.';
        }

        $fullNameTabs = [];
        $initialHereTabs = [];
        $lastNameTabs = [];
        $firstNameTabs = [];
        $emailAddressTabs = [];
        $textTabs = [];

        if ($assignment->travel->tar_required) {
            $fullNameTabs[] = (new FullName())
                ->setTabType('fullName')
                ->setDocumentId(1)
                ->setPageNumber(1)
                ->setFont('CourierNew')
                ->setFontColor('Black')
                ->setFontSize('Size12')
                ->setHeight(20)
                ->setWidth(300)
                ->setXPosition(115)
                ->setYPosition(125);

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

            $lastNameTabs[] = (new LastName())
                ->setTabType('lastName')
                ->setDocumentId(2)
                ->setPageNumber(1)
                ->setFont('CourierNew')
                ->setFontColor('Black')
                ->setFontSize('Size12')
                ->setHeight(20)
                ->setWidth(150)
                ->setXPosition(160)
                ->setYPosition(185);

            $firstNameTabs[] = (new FirstName())
                ->setTabType('firstName')
                ->setDocumentId(2)
                ->setPageNumber(1)
                ->setFont('CourierNew')
                ->setFontColor('Black')
                ->setFontSize('Size12')
                ->setHeight(20)
                ->setWidth(100)
                ->setXPosition(310)
                ->setYPosition(185);

            $emailAddressTabs[] = (new EmailAddress())
                ->setTabType('emailAddress')
                ->setDocumentId(2)
                ->setPageNumber(1)
                ->setFont('CourierNew')
                ->setFontColor('Black')
                ->setFontSize('Size12')
                ->setHeight(20)
                ->setWidth(100)
                ->setXPosition(370)
                ->setYPosition(235);

            $textTabs[] = (new Text())
                ->setTabType('text')
                ->setDocumentId(2)
                ->setPageNumber(1)
                ->setFont('CourierNew')
                ->setFontColor('Black')
                ->setFontSize('Size12')
                ->setHeight(20)
                ->setWidth(350)
                ->setXPosition(160)
                ->setYPosition(340)
                ->setRequired(true)
                ->setTooltip(
                    'Date of birth is required in this field. If you have a Known Traveler '.
                    'Number, PASS ID, Redress Control Number, SkyMiles number, or other identifier'.
                    ' relevant to air travel, enter it here as well.'
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
                            ->setEmailSubject(self::emailSubject($assignment->travel))
                            ->setEmailBody($emailBody)
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
                            ->setLastNameTabs($lastNameTabs)
                            ->setFirstNameTabs($firstNameTabs)
                            ->setEmailAddressTabs($emailAddressTabs)
                            ->setTextTabs($textTabs)
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
                                    ->setWidth(500)
                                    ->setXPosition(115)
                                    ->setYPosition(160)
                                    ->setValue($assignment->travel->destination),
                                (new Text())
                                    ->setTabType('text')
                                    ->setDocumentId(1)
                                    ->setPageNumber(1)
                                    ->setFont('CourierNew')
                                    ->setFontColor('Black')
                                    ->setFontSize('Size12')
                                    ->setHeight(20)
                                    ->setWidth(500)
                                    ->setXPosition(115)
                                    ->setYPosition(190)
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
                                    ->setXPosition(90)
                                    ->setYPosition(370)
                                    ->setValue('$'.$assignment->travel->tar_lodging),
                                (new Text())
                                    ->setTabType('text')
                                    ->setDocumentId(1)
                                    ->setPageNumber(1)
                                    ->setFont('CourierNew')
                                    ->setFontColor('Black')
                                    ->setFontSize('Size12')
                                    ->setHeight(20)
                                    ->setWidth(150)
                                    ->setXPosition(90)
                                    ->setYPosition(470)
                                    ->setValue($assignment->travel->tar_project_number),
                                (new Text())
                                    ->setTabType('text')
                                    ->setDocumentId(1)
                                    ->setPageNumber(1)
                                    ->setFont('CourierNew')
                                    ->setFontColor('Black')
                                    ->setFontSize('Size12')
                                    ->setHeight(20)
                                    ->setWidth(100)
                                    ->setXPosition(150)
                                    ->setYPosition(510)
                                    ->setValue($assignment->user->emergency_contact_phone),
                                (new Text())
                                    ->setTabType('text')
                                    ->setDocumentId(1)
                                    ->setPageNumber(1)
                                    ->setFont('CourierNew')
                                    ->setFontColor('Black')
                                    ->setFontSize('Size12')
                                    ->setHeight(20)
                                    ->setWidth(100)
                                    ->setXPosition(280)
                                    ->setYPosition(310)
                                    ->setValue('$'.$assignment->travel->tar_registration),
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
        $textTabs = [
            (new Text())
                ->setTabType('text')
                ->setDocumentId(2)
                ->setPageNumber(1)
                ->setFont('CourierNew')
                ->setFontColor('Black')
                ->setFontSize('Size12')
                ->setHeight(20)
                ->setWidth(100)
                ->setXPosition(160)
                ->setYPosition(235)
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
                ->setXPosition(185)
                ->setYPosition(260)
                ->setValue($assignment->travel->tar_purpose),
            (new Text())
                ->setTabType('text')
                ->setDocumentId(2)
                ->setPageNumber(1)
                ->setFont('CourierNew')
                ->setFontColor('Black')
                ->setFontSize('Size12')
                ->setHeight(20)
                ->setWidth(100)
                ->setXPosition(160)
                ->setYPosition(370)
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
                ->setWidth(100)
                ->setXPosition(160)
                ->setYPosition(435)
                ->setValue($assignment->user->employee_id);
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
                    )
            );
    }

    /**
     * Builds the array of documents for this travel assignment.
     *
     * @return array<\DocuSign\eSign\Model\Document>
     */
    private static function travelAssignmentDocuments(TravelAssignment $assignment): array
    {
        $documents = [];

        if ($assignment->travel->tar_required === true) {
            $documents[] = self::travelInformationFormDocument($assignment);
        }

        if ($assignment->travel->needs_airfare_form) {
            $documents[] = self::directBillAirfareFormDocument($assignment);
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
                self::emailSubject($envelope->signable->travel).' for '.$envelope->signedBy->full_name
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
                            ->setReminderDelay(1)
                            ->setReminderFrequency(1)
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
