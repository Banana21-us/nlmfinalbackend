<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class requestfile extends Model
{
    /** @use HasFactory<\Database\Factories\RequestfileFactory> */
    use HasFactory;
    protected $fillable =[
        'userid',
        'description',
        'file',
        'time',
        
    ];
}
