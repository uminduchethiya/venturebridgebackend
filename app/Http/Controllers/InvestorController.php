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

        // Fetch the matches along with the related documents
        $matches = InvestorStartupMatch::with([
            'startup' => function ($query) {
                $query->select('id', 'country', 'industry', 'user_id')
                    ->with(['user:id,email,company_name']);
            },
            'documents' => function ($query) {
                // Load the investor startup documents for each match
                $query->select('match_id', 'status');
            }
        ])
            ->where('investor_id', $userId)
            ->latest()
            ->get();

        // Log the matches with their documents
        Log::info("Investor matches: " . $matches->toJson());

        // Process the matches to filter out the email and company_name based on status
        $matches->transform(function ($match) {
            // Get the status of the related documents (if any)
            $documentStatus = $match->documents->first()->status ?? null;

            // Only include the email and company_name if the document status is "approved"
            if ($documentStatus !== 'approved') {
                // Hide email and company_name when status is not approved
                unset($match->startup->user->email);
                unset($match->startup->user->company_name);
            }

            return $match;
        });

        // Return the processed matches as JSON
        return response()->json($matches);
    }

    public function removeMatchedStartup($id)
    {
        $userId = Auth::id();

        // Find the match and ensure it belongs to the authenticated investor
        $match = InvestorStartupMatch::where('id', $id)
            ->where('investor_id', $userId)
            ->first();

        if (!$match) {
            return response()->json(['message' => 'Match not found or unauthorized.'], 404);
        }

        $match->delete();

        return response()->json(['message' => 'Match successfully removed.']);
    }



}
