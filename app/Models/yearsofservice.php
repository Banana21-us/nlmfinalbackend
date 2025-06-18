<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class yearsofservice extends Model
{
    /** @use HasFactory<\Database\Factories\YearsofserviceFactory> */
    use HasFactory;
    protected $fillable =[
        'userid',
        'organization',
        'start_date',
        'end_date',
    ];
}
