<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Document;
use App\Models\User;
class DocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

    foreach ($users as $user) {
        Document::create([
            'user_id' => $user->id,
            'title' => $user->type === 'startup' ? 'Startup Pitch' : 'Investor Profile',
            'description' => 'Sample document for matching',
            'file_url' => 'https://example.com/docs/sample.pdf',
            'document_type' => $user->type === 'startup' ? 'pitch_deck' : 'profile',
        ]);
    }
    }
}
