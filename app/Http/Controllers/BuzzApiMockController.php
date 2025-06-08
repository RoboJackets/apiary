<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BuzzApiMockController
{
    public function anything(Request $request, string $resource, string $action): JsonResponse
    {
        if (
            config('buzzapi.app_id') === null ||
            config('buzzapi.app_id') === '' ||
            config('buzzapi.app_password') === null ||
            config('buzzapi.app_password') === ''
        ) {
            return response()->json(
                data: [
                    'api_error_info' => [
                        'message' => 'credentials not configured for mock',
                    ],
                ],
                status: 401
            );
        }

        if (
            $request->input('api_app_id') !== config('buzzapi.app_id') ||
            $request->input('api_app_password') !== config('buzzapi.app_password')
        ) {
            return response()->json(
                data: [
                    'api_error_info' => [
                        'message' => 'credentials do not match config for mock',
                    ],
                ],
                status: 401
            );
        }

        $results = [];
        $gtid = $request->input('gtid');
        $uid = $request->input('uid');

        if ($gtid !== null) {
            if (User::where('gtid', '=', $gtid)->exists()) {
                $user = User::where('gtid', '=', $gtid)->sole();

                $results[] = [
                    'eduPersonPrimaryAffiliation' => $user->primary_affiliation,
                    'givenName' => $user->first_name,
                    'gtAccountEntitlement' => [],
                    'gtGTID' => $user->gtid,
                    'gtPersonDirectoryId' => $user->gtDirGUID,
                    'gtPrimaryGTAccountUsername' => $user->uid,
                    'mail' => $user->gt_email,
                    'sn' => $user->last_name,
                    'uid' => $user->uid,
                    'eduPersonScopedAffiliation' => [],
                ];
            } else {
                $results[] = [
                    'eduPersonPrimaryAffiliation' => 'student',
                    'givenName' => 'George',
                    'gtAccountEntitlement' => [],
                    'gtGTID' => strval($gtid),
                    'gtPersonDirectoryId' => bin2hex(openssl_random_pseudo_bytes(32)),
                    'gtPrimaryGTAccountUsername' => 'gburdell'.substr(strval($gtid), -6),
                    'mail' => 'gburdell'.substr(strval($gtid), -6).'@gatech.edu',
                    'sn' => 'Burdell',
                    'uid' => 'gburdell'.substr(strval($gtid), -6),
                    'eduPersonScopedAffiliation' => [],
                ];
            }
        } elseif ($uid !== null && User::where('uid', '=', $uid)->exists()) {
            $user = User::where('uid', '=', $uid)->sole();

            $results[] = [
                'eduPersonPrimaryAffiliation' => $user->primary_affiliation,
                'givenName' => $user->first_name,
                'gtAccountEntitlement' => [],
                'gtGTID' => $user->gtid,
                'gtPersonDirectoryId' => $user->gtDirGUID,
                'gtPrimaryGTAccountUsername' => $user->uid,
                'mail' => $user->gt_email,
                'sn' => $user->last_name,
                'uid' => $user->uid,
                'eduPersonScopedAffiliation' => [],
            ];
        }

        return response()->json([
            'api_result_data' => $results,
            'api_buzzapi_logs' => [
                'This response was generated from a mock endpoint for demonstration purposes',
            ],
            'api_provider_logs' => [],
        ]);
    }
}
