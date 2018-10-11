<?php

namespace App\Traits;

use App\User;

trait AuthorizeInclude
{
    /**
     * Given an array or comma-separated string of requested relationships to be included,
     * return an array of those allowed based on the requester's permissions.
     * @param $class string Class
     * @param $requestedInclude array of relationship names
     * @return array relationships to include
     */
    public function authorizeInclude($class, $requestedInclude = null)
    {
        // If the user doesn't request anything, we don't need to authorize anything
        if (is_null($requestedInclude)) {
            return [];
        }

        $allowedInclude = [];
        $uid = \Auth::user()->uid;
        \Log::debug(__METHOD__.": Checking authorization of $uid for $class");

        // Get permission mapping from the target model class
        $model = new $class();
        if (method_exists($model, 'getRelationshipPermissionMap')) {
            $relationPermMap = $model->getRelationshipPermissionMap();
        } else {
            \Log::notice(__METHOD__.": No relationship permission map for $class, assuming default naming");
            $relationPermMap = [];
        }

        if (is_array($requestedInclude)) {
            //this is fine
        } elseif (is_string($requestedInclude)) {
            // Convert comma-separated string to an array
            $requestedInclude = explode(',', $requestedInclude);
        } else {
            $type = gettype($requestedInclude);
            \Log::warning(__METHOD__.": Received invalid $type of relationships to include");

            return [];
        }

        // If the user asks for it and has permission to read it, give it to them.
        foreach ($requestedInclude as $include) {
            // Use either the predefined permission name or assume default naming convention
            $permission = array_key_exists($include, $relationPermMap) ?
                $relationPermMap[$include] : $this->camelToDashed($include);

            if (\Auth::user()->can("read-$permission")) {
                $allowedInclude[] = $include;
            }
        }
        \Log::debug(__METHOD__.": Authorized of $uid completed for $class - ".json_encode($allowedInclude));

        return $allowedInclude;
    }

    /**
     * Converts a string in camelCase to dashed-format
     * Ex. duesTransactions -> dues-transactions.
     * @param $string string to convert
     * @return string result
     */
    private function camelToDashed($string)
    {
        return strtolower(preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1-', $string));
    }
}
