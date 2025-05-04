<?php

namespace App\Http\Controllers;

use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Checkout\Session as StripeSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Package;
use App\Models\Payment;
use App\Models\User;

class StripeController extends Controller
{
    public function createCheckoutSession(Request $request)
    {
        $request->validate([
            'package_id' => 'required|exists:packages,id',
        ]);

        if (!Auth::check()) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        try {
            $package = Package::findOrFail($request->package_id);

            $stripeSecret = config('services.stripe.secret');
            if (!$stripeSecret || $stripeSecret === 'fallback_if_missing') {
                Log::error('Stripe secret key is not set.');
                return response()->json(['error' => 'Stripe key not set'], 500);
            }

            Stripe::setApiKey($stripeSecret);

            $successUrl = 'http://localhost:5173/paymentsuccess?session_id={CHECKOUT_SESSION_ID}&package_id=' . $package->id;
            $cancelUrl = 'http://localhost:5173/paymentcancel';

            $session = StripeSession::create([
                'payment_method_types' => ['card'],
                'line_items' => [
                    [
                        'price_data' => [
                            'currency' => 'usd',
                            'product_data' => [
                                'name' => $package->name,
                            ],
                            'unit_amount' => $package->price * 100,
                        ],
                        'quantity' => 1,
                    ]
                ],
                'mode' => 'payment',
                'client_reference_id' => Auth::id(),
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
            ]);

            Log::info('Stripe checkout session created', [
                'user_id' => Auth::id(),
                'session_id' => $session->id
            ]);

            return response()->json([
                'id' => $session->id,
                'url' => $session->url
            ]);
        } catch (\Exception $e) {
            Log::error('Stripe session creation failed', ['message' => $e->getMessage()]);
            return response()->json([
                'error' => 'Stripe error',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function paymentSuccess(Request $request)
    {
        try {
            $sessionId = $request->get('session_id');
            $packageId = $request->get('package_id');

            if (!$sessionId || !$packageId) {
                return response()->json(['error' => 'Missing parameters'], 400);
            }

            Stripe::setApiKey(config('services.stripe.secret'));

            $session = StripeSession::retrieve($sessionId);
            if (!$session) {
                Log::error('Stripe session not found', ['session_id' => $sessionId]);
                return response()->json(['error' => 'Stripe session not found'], 400);
            }

            // Retrieve user
            $user = Auth::user() ?? User::find($session->client_reference_id);
            if (!$user) {
                Log::error('User not found', ['client_reference_id' => $session->client_reference_id]);
                return response()->json(['error' => 'User not found'], 401);
            }

            $package = Package::findOrFail($packageId);

            // Check for existing payment to avoid duplicate processing
            $existingPayment = Payment::where('user_id', $user->id)
                ->where('package_id', $package->id)
                ->where('status', 'success')
                ->first();

            if ($existingPayment) {
                Log::info('Duplicate payment attempt detected', [
                    'user_id' => $user->id,
                    'package_id' => $package->id
                ]);
                return response()->json([
                    'message' => 'Payment already processed.',
                    'already_processed' => true
                ], 200);
            }

            // Retrieve payment intent
            $paymentIntent = PaymentIntent::retrieve($session->payment_intent);
            Log::info('Payment intent status', ['status' => $paymentIntent->status]);

            if ($paymentIntent->status !== 'succeeded') {
                Log::error('Payment intent not successful', ['status' => $paymentIntent->status]);
                return response()->json(['error' => 'Payment failed or not processed'], 400);
            }

            // Attach package to user if not already
            $alreadyActivated = DB::table('package_user')
                ->where('user_id', $user->id)
                ->where('package_id', $package->id)
                ->exists();
            // Attach package to user if not already
            $alreadyActivated = DB::table('package_user')
                ->where('user_id', $user->id)
                ->where('package_id', $package->id)
                ->exists();

            if (!$alreadyActivated) {
                $user->packages()->attach($package->id, [
                    'remaining_posts' => (int) $package->features,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Save payment record
            Payment::create([
                'user_id' => $user->id,
                'package_id' => $package->id,
                'amount' => $package->price,
                'status' => 'success',
            ]);

            Log::info('Payment processed and saved', [
                'user_id' => $user->id,
                'package_id' => $package->id
            ]);

            return response()->json([
                'message' => 'Payment successful and package activated!'
            ]);
        } catch (\Exception $e) {
            Log::error('Payment success error', ['message' => $e->getMessage()]);
            return response()->json([
                'error' => 'Server Error',
                'details' => $e->getMessage(),
            ], 500);
        }
    }
}
