<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Str;
class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

    foreach ($users as $user) {
        Payment::create([
            'user_id' => $user->id,
            'payment_method' => 'credit_card',
            'amount' => rand(100, 500),
            'transaction_id' => Str::uuid(),
            'status' => 'success',
        ]);
    }
    }
}
