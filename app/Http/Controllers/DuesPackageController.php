<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreDuesPackageRequest;
use App\Http\Requests\UpdateDuesPackageRequest;
use App\Http\Resources\DuesPackage as DuesPackageResource;
use App\Models\DuesPackage;
use App\Util\AuthorizeInclude;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DuesPackageController extends Controller
{
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
     */
    public function index(Request $request): JsonResponse
    {
        $include = $request->input('include');
        $packages = DuesPackage::with(AuthorizeInclude::authorize(DuesPackage::class, $include))->get();

        return response()->json(['status' => 'success', 'dues_packages' => DuesPackageResource::collection($packages)]);
    }

    /**
     * Display a listing of active DuesPackages.
     */
    public function indexActive(Request $request): JsonResponse
    {
        $include = $request->input('include');
        $packages = DuesPackage::with(AuthorizeInclude::authorize(DuesPackage::class, $include))->active()->get();

        return response()->json(['status' => 'success', 'dues_packages' => DuesPackageResource::collection($packages)]);
    }

    /**
     * Display a listing of DuesPackages that are available for purchase.
     */
    public function indexAvailable(Request $request): JsonResponse
    {
        $include = $request->input('include');
        $packages = DuesPackage::with(AuthorizeInclude::authorize(DuesPackage::class, $include))
            ->availableForPurchase()
            ->get();

        return response()->json(['status' => 'success', 'dues_packages' => DuesPackageResource::collection($packages)]);
    }

    /**
     * Display a listing of DuesPackages that this user can purchase.
     */
    public function indexUserCanPurchase(Request $request): JsonResponse
    {
        $include = $request->input('include');
        $packages = DuesPackage::with(AuthorizeInclude::authorize(DuesPackage::class, $include))
            ->userCanPurchase($request->user())
            ->get();

        return response()->json(['status' => 'success', 'dues_packages' => DuesPackageResource::collection($packages)]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDuesPackageRequest $request): JsonResponse
    {
        $package = DuesPackage::create($request->validated());

        $dbp = DuesPackage::findOrFail($package->id);

        return response()->json(['status' => 'success', 'dues_package' => new DuesPackageResource($dbp)], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, DuesPackage $package): JsonResponse
    {
        $include = $request->input('include');
        $package = DuesPackage::with(AuthorizeInclude::authorize(DuesPackage::class, $include))->find($package->id);
        if ($package !== null) {
            return response()->json(['status' => 'success', 'dues_package' => new DuesPackageResource($package)]);
        }

        return response()->json(['status' => 'error', 'message' => 'DuesPackage not found.'], 404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDuesPackageRequest $request, DuesPackage $package): JsonResponse
    {
        $package->update($request->validated());

        $package = DuesPackage::find($package->id);
        if ($package === null) {
            return response()->json(['status' => 'success', 'dues_package' => new DuesPackageResource($package)]);
        }

        return response()->json(['status' => 'error', 'message' => 'Unknown error.'], 500);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DuesPackage $package): JsonResponse
    {
        if ($package->delete() === true) {
            return response()->json(['status' => 'success', 'message' => 'DuesPackage deleted.']);
        }

        return response()->json(['status' => 'error',
            'message' => 'DuesPackage does not exist or was previously deleted.',
        ], 422);
    }
}
