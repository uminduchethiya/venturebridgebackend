<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StartupToMatchInvestorDocument extends Model
{
    use HasFactory;
    protected $fillable = [
        'match_id', 'pitch_deck', 'other_document', 'signature','status'
    ];
    public function match()
    {
        return $this->belongsTo(StartupToMatchInvestor::class, 'match_id');
    }

}
