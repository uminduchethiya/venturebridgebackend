<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'name', 'price', 'duration', 'features', 'status'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'package_user')
            ->withPivot('remaining_posts')
            ->withTimestamps();
    }



}
