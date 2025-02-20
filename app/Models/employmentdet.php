<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class employmentdet extends Model
{
    use HasFactory;

    protected $table = 'employmentdets'; // Specify the table name

    protected $fillable = [
        'userid',
        'position',
        'organization',
        'dateofemp',
    ];

    /**
     * Get the user associated with this employment detail.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'userid');
    }
}
