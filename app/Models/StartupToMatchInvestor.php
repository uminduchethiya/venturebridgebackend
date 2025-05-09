<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StartupToMatchInvestor extends Model
{
    use HasFactory;
    protected $table = 'startup_to_match_investor';

    protected $fillable = [
        'startup_user_id',
        'investor_user_id',
    ];

    // Define the relationship with the Startup model
    public function startup()
    {
        return $this->belongsTo(Startup::class);
    }

    // Define the relationship with the User model (investors)
    public function investor()
    {
        return $this->belongsTo(User::class, 'investor_user_id');
    }

    public function startupUser()
    {
        return $this->belongsTo(User::class, 'startup_user_id');
    }
    public function documents()
{
    return $this->hasMany(StartupToMatchInvestorDocument::class, 'match_id');
}


}
