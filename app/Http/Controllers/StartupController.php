<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Startup;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
class StartupController extends Controller
{
    public function storeStartup(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user || $user->type !== 'startup') {
                return response()->json([
                    'message' => 'Only startup users can submit this form.'
                ], 403);
            }

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'founding_year' => 'required|nullable|digits:4|integer|min:1900|max:' . date('Y'),
                'country' => 'required|nullable|string|max:100',
                'city' => 'required|nullable|string|max:100',
                'business_type' => 'required|nullable|string|max:100',
                'product_type' => 'required|nullable|string|max:100',
                'company_usage' => 'required|nullable|string|max:100',
                'annual_revenue' => 'required|nullable|numeric',
                'mrr' => 'required|nullable|numeric',
                'price' => 'required|nullable|numeric',
                'employees_count' => 'required|nullable|string|max:100',
                'linkedin_url' => 'required|nullable|url',
                'facebook_url' => 'required|nullable|url',
                'twitter_url' => 'required|nullable|url',
            ]);

            $startup = Startup::create(array_merge(
                $validated,
                ['user_id' => $user->id]
            ));

            return response()->json([
                'message' => 'Startup submitted successfully.',
                'startup' => $startup
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


}
