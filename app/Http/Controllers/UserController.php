<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function getUsersList()
{
    $users = User::select('id', 'email', 'company_name', 'type', 'created_at')->get();

    return response()->json([
        'status' => true,
        'message' => 'Users fetched successfully.',
        'users' => $users
    ], 200);
}
}
