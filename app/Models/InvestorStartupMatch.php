<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvestorStartupMatch extends Model
{
    use HasFactory;
    protected $fillable = ['investor_id', 'startup_id'];


    public function investor()
    {
        return $this->belongsTo(User::class, 'investor_id');
    }

    public function startup()
    {
        return $this->belongsTo(Startup::class, 'startup_id');
    }

}
