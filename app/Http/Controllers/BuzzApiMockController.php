<?php

declare(strict_types=1);

// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter
// phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BuzzApiMockController extends Controller
{
    public function anything(Request $request, string $resource, string $action): JsonResponse
    {
        $gtid = $request->input('gtid');
        $results = [];

        if (null !== $gtid) {
            $results[] = [
                'eduPersonPrimaryAffiliation' => 'student',
                'givenName' => 'George',
                'gtAccountEntitlement' => [],
                'gtGTID' => strval($gtid),
                'gtPersonDirectoryId' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ012345',
                'gtPrimaryGTAccountUsername' => 'gburdell'.substr(strval($gtid), -6),
                'mail' => 'gburdell'.substr(strval($gtid), -6).'@gatech.edu',
                'sn' => 'Burdell',
                'uid' => 'gburdell'.substr(strval($gtid), -6),
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
