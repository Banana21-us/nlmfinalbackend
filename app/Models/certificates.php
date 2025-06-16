<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class certificates extends Model
{
    /** @use HasFactory<\Database\Factories\CertificatesFactory> */
    use HasFactory;
    protected $fillable =[
        'userid',
        'name',
        'file'
    ];
    public function user()
{
    return $this->belongsTo(User::class, 'userid');
}
}
