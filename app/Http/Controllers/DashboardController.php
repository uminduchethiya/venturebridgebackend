<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Matching;
class DashboardController extends Controller
{
    public function dashboardSummary()
{
    $totalStartups = User::where('type', 'startup')->count();
    $totalInvestors = User::where('type', 'investor')->count();
    $totalMatched = Matching::where('status', 'matched')->count();
    $totalRejected = Matching::where('status', 'rejected')->count();

    return response()->json([
        'status' => true,
        'message' => 'Dashboard summary loaded.',
        'data' => [
            'startups' => $totalStartups,
            'investors' => $totalInvestors,
            'matched' => $totalMatched,
            'rejected' => $totalRejected,
        ]
    ], 200);
}
}
