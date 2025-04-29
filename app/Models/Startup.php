<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Startup extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'title',
        'founding_year',
        'country',
        'city',
        'business_type',
        'product_type',
        'Industry',
        'annual_revenue',
        'mrr',
        'price',
        'employees_count',
        'linkedin_url',
        'facebook_url',
        'twitter_url',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
