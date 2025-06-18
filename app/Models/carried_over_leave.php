<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class carried_over_leave extends Model
{
    /** @use HasFactory<\Database\Factories\CarriedOverLeaveFactory> */
    use HasFactory;
   protected $table = 'carried_over_leave';

    protected $fillable = [
        'user_id',
        'year',
        'days',
        'expires_at'
    ];

    protected $casts = [
        'expires_at' => 'date'
    ];
}
