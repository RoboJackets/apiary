<?php

declare(strict_types=1);

// phpcs:disable SlevomatCodingStandard.ControlStructures.RequireTernaryOperator

namespace App\Http\Controllers;

use App\DuesPackage;
use App\Http\Requests\StoreDuesPackageRequest;
use App\Http\Requests\UpdateDuesPackageRequest;
use App\Http\Resources\DuesPackage as DuesPackageResource;
use App\Traits\AuthorizeInclude;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DuesPackageController extends Controller
{
    use AuthorizeInclude;

    public function __construct()
    {
        $this->middleware(
            'permission:read-dues-packages',
            [
                'only' => [
                    'index',
                    'indexActive',
                    'indexAvailable',
                    'show',
                ],
            ]
        );
        $this->middleware('permission:create-dues-packages', ['only' => ['store']]);
        $this->middleware('permission:update-dues-packages', ['only' => ['update']]);
        $this->middleware('permission:delete-dues-packages', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $include = $request->input('include');
        $packages = DuesPackage::with($this->authorizeInclude(DuesPackage::class, $include))->get();

        return response()->json(['status' => 'success', 'dues_packages' => DuesPackageResource::collection($packages)]);
    }

    /**
     * Display a listing of active DuesPackages.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function indexActive(Request $request): JsonResponse
    {
        $include = $request->input('include');
        $packages = DuesPackage::with($this->authorizeInclude(DuesPackage::class, $include))->active()->get();

        return response()->json(['status' => 'success', 'dues_packages' => DuesPackageResource::collection($packages)]);
    }

    /**
     * Display a listing of DuesPackages that are available for purchase.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function indexAvailable(Request $request): JsonResponse
    {
        $include = $request->input('include');
        $packages = DuesPackage::with($this->authorizeInclude(DuesPackage::class, $include))
            ->availableForPurchase()
            ->get();

        return response()->json(['status' => 'success', 'dues_packages' => DuesPackageResource::collection($packages)]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\StoreDuesPackageRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreDuesPackageRequest $request): JsonResponse
    {
        try {
            $package = DuesPackage::create($request->all());
        } catch (QueryException $e) {
            Bugsnag::notifyException($e);
            $errorMessage = $e->errorInfo[2];

            return response()->json(['status' => 'error', 'message' => $errorMessage], 500);
        }

        if (is_numeric($package->id)) {
            $dbp = DuesPackage::findOrFail($package->id);

            return response()->json(['status' => 'success', 'dues_package' => new DuesPackageResource($dbp)], 201);
        }

        return response()->json(['status' => 'error', 'message' => 'Unknown error.'], 500);
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $include = $request->input('include');
        $package = DuesPackage::with($this->authorizeInclude(DuesPackage::class, $include))->find($id);
        if (null !== $package) {
            return response()->json(['status' => 'success', 'dues_package' => new DuesPackageResource($package)]);
        }

        return response()->json(['status' => 'error', 'message' => 'DuesPackage not found.'], 404);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\UpdateDuesPackageRequest $request
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateDuesPackageRequest $request, int $id): JsonResponse
    {
        $package = DuesPackage::find($id);
        if (! $package) {
            return response()->json(['status' => 'error', 'message' => 'DuesPackage not found.'], 404);
        }

        $package->update($request->all());

        $package = DuesPackage::find($package->id);
        if ($package) {
            return response()->json(['status' => 'success', 'dues_package' => new DuesPackageResource($package)]);
        }

        return response()->json(['status' => 'error', 'message' => 'Unknown error.'], 500);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $package = DuesPackage::find($id);
        if ($package->delete()) {
            return response()->json(['status' => 'success', 'message' => 'DuesPackage deleted.']);
        }

        return response()->json(['status' => 'error',
            'message' => 'DuesPackage does not exist or was previously deleted.',
        ], 422);
    }
}
