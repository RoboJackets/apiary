<?php

namespace App\Http\Controllers;

use App\DuesPackage;
use Illuminate\Http\Request;

class DuesPackageController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:read-dues-packages', ['only' => ['index', 'indexActive', 'indexAvailable', 'show']]);
        $this->middleware('permission:create-dues-packages', ['only' => ['store']]);
        $this->middleware('permission:update-dues-packages', ['only' => ['update']]);
        $this->middleware('permission:delete-dues-packages', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $packages = DuesPackage::all();

        return response()->json(['status' => 'success', 'dues_packages' => $packages]);
    }

    /**
     * Display a listing of active DuesPackages.
     */
    public function indexActive()
    {
        $activePackages = DuesPackage::active()->get();

        return response()->json(['status' => 'success', 'dues_packages' => $activePackages]);
    }

    /**
     * Display a listing of DuesPackages that are available for purchase.
     */
    public function indexAvailable()
    {
        $activePackages = DuesPackage::availableForPurchase()->get();

        return response()->json(['status' => 'success', 'dues_packages' => $activePackages]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string',
            'eligible_for_shirt' => 'boolean',
            'eligible_for_polo' => 'boolean',
            'effective_start' => 'required|date',
            'effective_end' => 'required|date',
            'cost' => 'required|numeric',
        ]);

        try {
            $package = DuesPackage::create($request->all());
        } catch (QueryException $e) {
            Bugsnag::notifyException($e);
            $errorMessage = $e->errorInfo[2];

            return response()->json(['status' => 'error', 'message' => $errorMessage], 500);
        }

        if (is_numeric($package->id)) {
            $dbPackage = DuesPackage::findOrFail($package->id);

            return response()->json(['status' => 'success', 'dues_package' => $dbPackage], 201);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Unknown error.'], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $package = DuesPackage::find($id);
        if ($package) {
            return response()->json(['status' => 'success', 'dues_package' => $package]);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Payment not found.'], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'string',
            'eligible_for_shirt' => 'boolean',
            'eligible_for_polo' => 'boolean',
            'effective_start' => 'date',
            'effective_end' => 'date',
            'cost' => 'numeric',
        ]);

        $package = DuesPackage::find($id);
        if ($package) {
            $package->update($request->all());
        } else {
            return response()->json(['status' => 'error', 'message' => 'DuesPackage not found.'], 404);
        }

        $package = DuesPackage::find($package->id);
        if ($package) {
            return response()->json(['status' => 'success', 'dues_package' => $package]);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Unknown error.'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $package = DuesPackage::find($id);
        $deleted = $package->delete();
        if ($deleted) {
            return response()->json(['status' => 'success', 'message' => 'DuesPackage deleted.']);
        } else {
            return response()->json(['status' => 'error',
                'message' => 'DuesPackage does not exist or was previously deleted.', ], 422);
        }
    }
}
