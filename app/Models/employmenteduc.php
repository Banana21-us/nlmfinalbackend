<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class employmenteduc extends Model
{
    use HasFactory;
    protected $table = 'employmenteducs';

    // Define fillable fields
    protected $fillable = [
        'userid',
        'levels',
        'year',
        'school',
        'degree',
    ];

    // Define the relationship with the User model
    public function user()
    {
        return $this->belongsTo(User::class, 'userid');
    }
}
