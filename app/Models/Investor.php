<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Investor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'founding_round',
        'investment_amount',
        'valuation',
        'number_of_investors',
        'founding_year',
        'growth_rate',
        'business_type',
        'product_type',
        'company_usage',
        'annual_revenue',
        'mrr',
        'employees_count',
        'price',
        'linkedin_url',
        'facebook_url',
        'twitter_url',
        'image', // if handling image uploads
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function userInfo()
{
    return $this->belongsTo(User::class, 'user_id');
}

}
