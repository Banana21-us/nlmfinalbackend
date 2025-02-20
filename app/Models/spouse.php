<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class spouse extends Model
{
    use HasFactory;

    protected $fillable =[
        'id',
        'userid',
        'name',
        'dateofmarriage'
    ];
}
