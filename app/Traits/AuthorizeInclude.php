<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

trait AuthorizeInclude
{
    /**
     * Given an array or comma-separated string of requested relationships to be included,
     * return an array of those allowed based on the requester's permissions.
     *
     * @return array<string> relationships to include
     */
    public function authorizeInclude(string $class, ?string $requestedInclude = null): array
    {
        // If the user doesn't request anything, we don't need to authorize anything
        if ($requestedInclude === null) {
            return [];
        }

        $allowedInclude = [];
        $uid = Auth::user()->uid;
        Log::debug(__METHOD__.': Checking authorization of '.$uid.' for '.$class);

        // Get permission mapping from the target model class
        $model = new $class();

        if (method_exists($model, 'getRelationshipPermissionMap')) {
            $relationPermMap = $model->getRelationshipPermissionMap();
        } else {
            Log::notice(__METHOD__.': No relationship permission map for '.$class.', assuming default naming');
            $relationPermMap = [];
        }

        // Convert comma-separated string to an array
        $requestedInclude = explode(',', $requestedInclude);

        // If the user asks for it and has permission to read it, give it to them.
        foreach ($requestedInclude as $include) {
            // Use either the predefined permission name or assume default naming convention
            $permission = array_key_exists(
                $include,
                $relationPermMap
            ) ? $relationPermMap[$include] : 'read-'.$this->camelToDashed($include);

            if (Auth::user()->cant($permission)) {
                Log::debug('User is missing permission: '.$permission);

                continue;
            }

            $allowedInclude[] = $include;
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
    private function camelToDashed(string $string): string
    {
        return strtolower(preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1-', $string));
    }
}
