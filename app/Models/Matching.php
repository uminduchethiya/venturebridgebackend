<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Matching extends Model
{
    use HasFactory;
    protected $fillable = ['startup_id', 'investor_id', 'status'];

public function startup()
{
    return $this->belongsTo(User::class, 'startup_id');
}

public function investor()
{
    return $this->belongsTo(User::class, 'investor_id');
}

}
