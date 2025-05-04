<?php

namespace App\Http\Controllers;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
class PackageController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'price' => 'required|numeric',
            'duration' => 'required|in:per_month,annually',
            'features' => 'required|in:2,5,10,12',
            'status' => 'required|in:active,inactive',
        ]);

        $package = Package::create([
            'user_id' => $request->user()->id,
            'name' => $request->name,
            'price' => $request->price,
            'duration' => $request->duration,
            'features' => $request->features,
            'status' => $request->status,
        ]);

        return response()->json(['message' => 'Package created', 'data' => $package], 201);
    }

    public function index()
    {
        try {
            $packages = Package::all();
            return response()->json(['data' => $packages], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $package = Package::findOrFail($id);
        $package->delete();

        return response()->json(['message' => 'Package deleted'], 200);
    }

    public function show($id)
    {
        $package = Package::findOrFail($id);
        return response()->json(['data' => $package], 200);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string',
            'price' => 'required|numeric',
            'duration' => 'required|in:per_month,annually',
            'features' => 'required|in:2,5,10,12',
            'status' => 'required|in:active,inactive',
        ]);

        $package = Package::findOrFail($id);
        $package->update([
            'name' => $request->name,
            'price' => $request->price,
            'duration' => $request->duration,
            'features' => $request->features,
            'status' => $request->status,
        ]);

        return response()->json(['message' => 'Package updated', 'data' => $package], 200);
    }
    public function activate(Request $request)
    {
        try {
            $request->validate([
                'package_id' => 'required|exists:packages,id',
            ]);

            $package = Package::findOrFail($request->package_id);
            $user = Auth::user();

            // Attach package with post limit from features
            $user->packages()->attach($package->id, [
                'remaining_posts' => (int) $package->features
            ]);

            return response()->json([
                'message' => 'Package activated successfully!',
                'package' => $package->name,
                'remaining_posts' => (int) $package->features
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Server Error',
                'details' => $e->getMessage(),
            ], 500);
        }
    }
    // PackageController.php
    public function getActivePackage(Request $request)
    {
        // Log the request to check incoming user data
        Log::info('Received request to get active package', ['user' => Auth::user()]);

        // Authenticate user
        $user = Auth::user();
        if (!$user) {
            Log::error('User not authenticated');
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        // Log the user ID to track which user is being processed
        Log::info('Authenticated User ID: ' . $user->id);

        // Get the active package that the user has attached to them
        $packageUser = DB::table('package_user')
            ->where('user_id', $user->id)
            ->whereNull('deleted_at') // If you are using soft deletes
            ->latest() // Assuming the latest package is the active one
            ->first();

        // Log the result of the query to see if a package is found
        Log::info('Package User Data: ', ['packageUser' => $packageUser]);

        if (!$packageUser) {
            Log::warning('No active subscription found for user', ['user_id' => $user->id]);
            return response()->json(['message' => 'No active subscription found'], 404);
        }

        // Get package details
        $package = Package::find($packageUser->package_id);

        // Log package details or if the package is not found
        if (!$package) {
            Log::error('Package not found for package_id: ' . $packageUser->package_id);
            return response()->json(['error' => 'Package not found'], 404);
        }

        // Log the successful response
        Log::info('Returning active package data for user: ' . $user->id, ['package' => $package]);

        return response()->json([
            'data' => [
                'package' => $package,
                'remaining_posts' => $packageUser->remaining_posts,
            ]
        ]);
    }



}
