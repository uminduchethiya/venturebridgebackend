<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Startup;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use App\Models\StartupToMatchInvestor;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\User;
class StartupController extends Controller
{
    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user || $user->type !== 'startup') {
            Log::warning('Unauthorized access attempt by user ID: ' . ($user->id ?? 'Guest'));
            return response()->json([
                'message' => 'Only startup users can submit this form.'
            ], 403);
        }

        Log::info('Startup form submission by user ID: ' . $user->id);

        $request->validate([
            'founding_year' => 'nullable|integer',
            'country' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'industry' => 'nullable|string|max:255',
            'sub_vertical' => 'nullable|string|max:255',
            'investment_type' => 'nullable|string|max:255',
            'price' => 'nullable|numeric',
            'annual_revenue' => 'nullable|numeric',
            'mrr' => 'nullable|numeric',
            'employees_count' => 'nullable|string|max:255',
            'linkedin_url' => 'nullable|url|max:255',
            'facebook_url' => 'nullable|url|max:255',
            'twitter_url' => 'nullable|url|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('startups', 'public');
            Log::info('Image uploaded to: ' . $imagePath);
        }

        $startup = Startup::create([
            'founding_year' => $request->input('founding_year'),
            'city' => $request->input('city'),
            'country' => $request->input('country'),
            'industry' => $request->input('industry'),
            'sub_vertical' => $request->input('sub_vertical'),
            'investment_type' => $request->input('investment_type'),
            'price' => $request->input('price'),
            'annual_revenue' => $request->input('annual_revenue'),
            'mrr' => $request->input('mrr'),
            'employees_count' => $request->input('employees_count'),
            'linkedin_url' => $request->input('linkedin_url'),
            'facebook_url' => $request->input('facebook_url'),
            'twitter_url' => $request->input('twitter_url'),
            'image' => $imagePath,
            'user_id' => $user->id,
        ]);

        Log::info('Startup created with ID: ' . $startup->id);

        // 🔁 Call prediction API
        try {
            Log::info('Calling prediction API...');

            $response = Http::post('http://127.0.0.1:5001/predict', [
                'Year' => $request->input('founding_year'),
                'Industry Vertical' => $request->input('industry'),
                'SubVertical' => $request->input('sub_vertical'),
                'City Location' => $request->input('city'),
                'InvestmentnType' => $request->input('investment_type'),
                'Amount in USD' => $request->input('price'),
            ]);

            Log::info('Prediction API response: ' . $response->body());

            if ($response->successful()) {
                $topInvestors = $response->json()['top_investors'] ?? [];

                if ($topInvestors) {
                    Log::info('Top investors predicted: ' . implode(', ', $topInvestors));

                    foreach ($topInvestors as $investorName) {
                        $investor = User::where('type', 'investor')
                            ->where('company_name', 'LIKE', '%' . $investorName . '%')
                            ->first();

                        if ($investor) {
                            Log::info('Matching investor found: ID ' . $investor->id);

                            // Store the match for each investor
                            StartupToMatchInvestor::create([
                                'startup_user_id' => $user->id,
                                'investor_user_id' => $investor->id
                            ]);
                        } else {
                            Log::warning('No matching investor found for predicted: ' . $investorName);
                        }
                    }
                } else {
                    Log::warning('No predicted investors returned from prediction API.');
                }
            } else {
                Log::error('Prediction API failed: ' . $response->status());
            }
        } catch (\Exception $e) {
            Log::error('Prediction API call failed', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);
        }

        return response()->json([
            'message' => 'Startup created successfully!',
            'startup' => $startup
        ]);
    }

    public function getMatchedInvestors(Request $request)
    {
        $user = Auth::user();

        if (!$user || $user->type !== 'startup') {
            return response()->json([
                'message' => 'Only startup users can view matched investors.'
            ], 403);
        }

        $matchedInvestorIds = StartupToMatchInvestor::where('startup_user_id', $user->id)
            ->pluck('investor_user_id');

        // Return full User records for investors
        $matchedInvestors = User::whereIn('id', $matchedInvestorIds)
            ->where('type', 'investor') // optional but safe
            ->get();

        return response()->json([
            'matched_investors' => $matchedInvestors
        ]);
    }

}
