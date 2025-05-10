<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvestorStartupDocument extends Model
{
    use HasFactory;
    protected $fillable = [
        'match_id',
        'pitch_deck',
        'other_document',
        'signature',
        'status',
        // Add any other fields you need to be mass assignable
    ];

    // In the InvestorStartupMatch model
// In App\Models\InvestorStartupMatch.php
public function match()
    {
        return $this->belongsTo(InvestorStartupMatch::class, 'match_id');
    }

}
