<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class leavetype extends Model
{
    use HasFactory;
    protected $fillable =[
        'id',
        'type',
        'days_allowed',
        'description',
    ];
}
