<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class workstatus extends Model
{
    /** @use HasFactory<\Database\Factories\WorkstatusFactory> */
    use HasFactory;
    protected $fillable =[
        'id',
        'name',
    ];
}
