<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvestorStartupMatch extends Model
{
    use HasFactory;
    protected $fillable = ['investor_id', 'startup_id'];
}
