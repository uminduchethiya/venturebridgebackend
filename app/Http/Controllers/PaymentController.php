<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
class PaymentController extends Controller
{
    public function getPaymentList()
{
    $payments = Payment::with('user:id,email')->get();

    return response()->json([
        'status' => true,
        'message' => 'Payment list fetched successfully.',
        'payments' => $payments
    ], 200);
}

}
