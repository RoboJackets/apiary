<?php

declare(strict_types=1);

// phpcs:disable SlevomatCodingStandard.Functions.DisallowNamedArguments.DisallowedNamedArgument
// phpcs:disable SlevomatCodingStandard.PHP.RequireExplicitAssertion.RequiredExplicitAssertion
// phpcs:disable Generic.Commenting.DocComment.MissingShort

namespace App\Models;

use App\Exceptions\DocuSignTokenUnavailable;
use App\Util\DocuSign;
use Carbon\Carbon;
use DocuSign\eSign\Client\Auth\OAuthToken;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * DocuSign tokens for the service account used to send envelopes.
 *
 * @property string $type
 * @property string $token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon $expires_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|DocuSignToken newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DocuSignToken newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DocuSignToken query()
 * @method static \Illuminate\Database\Eloquent\Builder|DocuSignToken whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocuSignToken whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocuSignToken whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocuSignToken whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocuSignToken whereUpdatedAt($value)
 *
 * @mixin \Barryvdh\LaravelIdeHelper\Eloquent
 */
class DocuSignToken extends Model
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public const ACCESS_TOKEN = 'access_token';

    public const CACHE_KEY = 'docusign_'.self::ACCESS_TOKEN;

    public const REFRESH_TOKEN = 'refresh_token';

    /**
     * The name of the database table for this model.
     *
     * @var string
     */
    protected $table = 'docusign_tokens';

    public static function accessToken(): string
    {
        $access_token = Cache::get(self::CACHE_KEY);

        if ($access_token === null) {
            $access_token = self::where('type', self::ACCESS_TOKEN)
                ->where('expires_at', '>', Carbon::now())
                ->first();

            if ($access_token === null) {
                $refresh_token = self::refreshToken();

                $docusign = DocuSign::getApiClient(withAccessToken: false);

                /** @var OAuthToken $tokens */
                $tokens = $docusign->refreshAccessToken(
                    client_id: config('docusign.client_id'),
                    client_secret: config('docusign.client_secret'),
                    refresh_token: $refresh_token
                )[0];

                self::storeTokens($tokens);

                return $tokens->getAccessToken();
            } else {
                Cache::put(self::CACHE_KEY, $access_token->token, $access_token->expires_at);

                return $access_token->token;
            }
        } else {
            return $access_token;
        }
    }

    public static function refreshToken(): string
    {
        $refresh_token = self::where('type', self::REFRESH_TOKEN)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if ($refresh_token === null) {
            throw new DocuSignTokenUnavailable('Refresh token is unavailable or expired');
        } else {
            return $refresh_token->token;
        }
    }

    /**
     * Store the provided tokens in the database.
     *
     * @phan-suppress PhanTypeInvalidLeftOperandOfNumericOp
     */
    public static function storeTokens(OAuthToken $tokens): void
    {
        self::upsert(
            [
                [
                    'type' => self::ACCESS_TOKEN,
                    'token' => $tokens->getAccessToken(),
                    'expires_at' => Carbon::now()->addSeconds($tokens->getExpiresIn() - 60),
                ],
                [
                    'type' => self::REFRESH_TOKEN,
                    'token' => $tokens->getRefreshToken(),
                    'expires_at' => Carbon::now()->addDays(29),
                ],
            ],
            [
                'type',
            ],
            [
                'token',
                'expires_at',
            ]
        );

        Cache::put(
            self::CACHE_KEY,
            $tokens->getAccessToken(),
            Carbon::now()->addSeconds($tokens->getExpiresIn() - 60)
        );
    }
}
