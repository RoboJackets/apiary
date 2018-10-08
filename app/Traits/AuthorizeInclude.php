<?php

namespace App\Traits;

use App\User;

trait AuthorizeInclude
{
    /**
     * Given an array of requested relationships to be included,
     * return an array of those allowed based on the requester's permissions
     * @param $class string Class
     * @param $requestedInclude array of relationship names
     * @return array relationships to include
     */
    public function authorizeInclude($class, $requestedInclude = null)
    {
        $allowedInclude = [];
        $uid = \Auth::user()->uid;
        \Log::debug(__METHOD__ . ": Checking authorization of $uid for $class");

        // Get permission mapping from the target model class
        $model = new $class();
        if (method_exists($model, 'getRelationshipPermissionMap')) {
            $relationPermMap = $model->getRelationshipPermissionMap();
        } else {
            \Log::error(__METHOD__ . ": Unable to retrieve relationship permission map for $class");
            return [];
        }

        if (is_array($requestedInclude)) {
            //this is fine
        } elseif (is_string($requestedInclude)) {
            // Convert comma-separated string to an array
            $requestedInclude = explode(',', $requestedInclude);
        } else {
            $type = gettype($requestedInclude);
            \Log::warning(__METHOD__ . ": Received invalid $type of relationships to include");
            return [];
        }

        // If the user asks for it and has permission to read it, give it to them.
        foreach ($requestedInclude as $include) {
            if (array_key_exists($include, $relationPermMap)) {
                $permission = $relationPermMap[$include];
                if (\Auth::user()->can("read-$permission")) {
                    $allowedInclude[] = $include;
                }
            }
        }
        \Log::debug(__METHOD__ . ": Authorized of $uid completed for $class - " . json_encode($allowedInclude));
        return $allowedInclude;
    }

}