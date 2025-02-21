<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    use HasFactory;

    protected $fillable = ['userid', 'leavetypeid', 'from', 'to', 'reason', 'status'];

    // Relationship with User
    public function user()
    {
        return $this->belongsTo(User::class, 'userid');
    }

    // Relationship with LeaveType
    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class, 'leavetypeid');
    }
}
