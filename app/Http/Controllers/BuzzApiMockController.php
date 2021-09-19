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

        if ('903000000' === $gtid) {
            $results[] = [
                'eduPersonPrimaryAffiliation' => 'student',
                'givenName' => 'George',
                'gtAccountEntitlement' => [],
                'gtGTID' => '903000000',
                'gtPersonDirectoryId' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ012345',
                'gtPrimaryGTAccountUsername' => 'gburdell3',
                'mail' => 'gburdell3@gatech.edu',
                'sn' => 'Burdell',
                'uid' => 'gburdell3',
            ];
        }

        return response()->json([
            'api_result_data' => [
                $results,
            ],
            'api_buzzapi_logs' => [
                'This response was generated from a mock endpoint for demonstration purposes',
            ],
            'api_provider_logs' => [],
        ]);
    }
}
