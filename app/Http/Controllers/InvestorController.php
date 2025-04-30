<?php

namespace App\Http\Controllers;

use App\Models\Investor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Startup;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use App\Models\InvestorStartupMatch;
class InvestorController extends Controller
{
    public function storeInvestor(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user || $user->type !== 'investor') {
                return response()->json([
                    'message' => 'Only Investors users can submit this form.'
                ], 403);
            }

            $validated = $request->validate([
                'founding_round' => 'required|string|max:255',
                'investment_amount' => 'required|numeric',
                'valuation' => 'required|numeric',
                'number_of_investors' => 'required|integer',
                'founding_year' => 'required|digits:4|integer|min:1900|max:' . date('Y'),
                'growth_rate' => 'required|numeric',
                'business_type' => 'required|string|max:100',
                'product_type' => 'required|string|max:100',
                'company_usage' => 'required|string|max:100',
                'annual_revenue' => 'required|numeric',
                'mrr' => 'required|numeric',
                'employees_count' => 'required|string|max:100',
                'price' => 'required|numeric',
                'linkedin_url' => 'required|url',
                'facebook_url' => 'required|url',
                'twitter_url' => 'required|url',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // if you upload an image
            ]);

            // If you want to handle image upload
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('investors', 'public');
                $validated['image'] = $imagePath;
            }

            $investor = Investor::create(array_merge(
                $validated,
                ['user_id' => $user->id]
            ));

            return response()->json([
                'message' => 'Investor submitted successfully.',
                'Investor' => $investor
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

    public function checkStartupMatch(Request $request)
    {
        Log::info('Checking startup match with request data:', $request->only(['country', 'industry']));

        $validated = $request->validate([
            'country' => 'required|string',
            'industry' => 'required|string',
        ]);

        $userId = Auth::id();
        Log::info("Authenticated user ID: {$userId}");

        $match = Startup::where('country', $validated['country'])
            ->where('industry', $validated['industry'])
            ->first();

        if ($match) {
            // Save the match to the pivot table
            InvestorStartupMatch::create([
                'investor_id' => $userId,
                'startup_id' => $match->id,
            ]);
        }

        return response()->json([
            'found' => (bool) $match,
            'user_id' => $userId,
            'startup' => $match ? [
                'id' => $match->id,
                'name' => $match->title,
                'country' => $match->country,
                'industry' => $match->industry,
            ] : null,
        ]);
    }

    public function getInvestorNotifications()
    {
        $userId = Auth::id();
        Log::info("Getting notifications for investor ID: {$userId}");

        $matches = InvestorStartupMatch::with(['startup:id,title,country,industry'])
                    ->where('investor_id', $userId)
                    ->latest()
                    ->get();

        Log::info("Investor matches: " . $matches->toJson());

        return response()->json($matches);
    }

}
