<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class events extends Model
{
    /** @use HasFactory<\Database\Factories\EventsFactory> */
    use HasFactory;

    protected $fillable = ['title', 'time', 'userid'];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class, 'userid');
    }

}
