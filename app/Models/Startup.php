<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Startup extends Model
{
    use HasFactory;
    protected $fillable = [
        'founding_year',
        'country',
        'city',
        'industry',
        'sub_vertical',
        'investment_type',
        'price',
        'annual_revenue',
        'mrr',
        'employees_count',
        'linkedin_url',
        'facebook_url',
        'twitter_url',
        'image',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, );
    }
}
