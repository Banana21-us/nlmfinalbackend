<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class empfamily extends Model
{
    use HasFactory;
    // protected $table = 'empfamilies';
    // Define fillable fields
    protected $fillable = [
        'userid',
        'children',
        'dateofbirth',
        'career',
    ];
}
