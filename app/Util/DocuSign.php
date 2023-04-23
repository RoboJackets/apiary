<?php

declare(strict_types=1);

// phpcs:disable Generic.Commenting.DocComment.MissingShort
// phpcs:disable Generic.NamingConventions.CamelCapsFunctionName.ScopeNotCamelCaps
// phpcs:disable SlevomatCodingStandard.Functions.DisallowNamedArguments.DisallowedNamedArgument
// phpcs:disable SlevomatCodingStandard.PHP.RequireExplicitAssertion.RequiredExplicitAssertion

namespace App\Util;

use App\Models\User;
use Carbon\Carbon;
use DocuSign\eSign\Client\ApiClient;
use DocuSign\eSign\Client\ApiException;
use DocuSign\eSign\Client\Auth\OAuth;
use DocuSign\eSign\Client\Auth\UserInfo;
use DocuSign\eSign\Configuration;
use Illuminate\Support\Facades\Cache;

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
}
