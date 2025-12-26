<?php

declare(strict_types=1);

namespace App\Util;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Guard;

class AuthorizeInclude
{
    /**
     * Given an array or comma-separated string of requested relationships to be included,
     * return an array of those allowed based on the requester's permissions.
     *
     * @param  class-string  $class  the class to check for permissions
     * @param  string|null  $requestedRelationships  the relationships that were requested
     * @return array<string> authorized relationships to include
     */
    public static function authorize(string $class, ?string $requestedRelationships = null): array
    {
        // If the user doesn't request anything, we don't need to authorize anything
        if ($requestedRelationships === null) {
            return [];
        }

        $allowedInclude = [];
        $uid = Auth::user()?->uid ?? Guard::getPassportClient(null)->getKey();
        Log::debug(__METHOD__.': Checking authorization of '.$uid.' for '.$class);
        // Get permission mapping from the target model class
        $model = new $class();

        if (defined($class.'::RELATIONSHIP_PERMISSIONS')) {
            $relationPermMap = $model::RELATIONSHIP_PERMISSIONS;
        } else {
            Log::notice(__METHOD__.': No relationship permission map for '.$class.', assuming default naming');
            $relationPermMap = [];
        }

        // Convert comma-separated string to an array
        $requestedRelationships = explode(',', $requestedRelationships);

        // If the user asks for it and has permission to read it, give it to them.
        foreach ($requestedRelationships as $include) {
            // Use either the predefined permission name or assume default naming convention
            $permission = array_key_exists(
                $include,
                $relationPermMap
            ) ? $relationPermMap[$include] : 'read-'.self::camelToDashed($include);

            if ((Auth::user()?->can($permission) ?? Guard::getPassportClient(null)?->can($permission)) === true) {
                $allowedInclude[] = $include;
            } else {
                Log::debug('User is missing permission: '.$permission);
            }
        }
        Log::debug(
            __METHOD__.': Authorized of '.$uid.' completed for '.$class.' - '.json_encode($allowedInclude)
        );

        return $allowedInclude;
    }

    /**
     * Converts a string in camelCase to dashed-format
     * Ex. duesTransactions -> dues-transactions.
     *
     * @param  string  $string  string to convert
     * @return string result
     */
    private static function camelToDashed(string $string): string
    {
        return strtolower(preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1-', $string));
    }
}
