<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveReq extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'userid', 
        'leavetypeid', 
        'from', 
        'to', 
        'reason',
        'DHead',
        'dept_head',
        'exec_sec',
        'president'
    ];
    protected $attributes = [
        'dept_head' => 'None',
    ];

    protected static function boot()
    {
        parent::boot();

        // Only set default status during creation
        static::creating(function ($model) {
            // Only set to Pending if DHead exists AND no status was explicitly set
            if ($model->DHead !== null && $model->dept_head === 'None') {
                $model->dept_head = 'Pending';
            }
            
            // Force None status if DHead is null
            if ($model->DHead === null) {
                $model->dept_head = 'None';
            }
        });

        // Prevent automatic status changes during updates
        static::updating(function ($model) {
            // If DHead is null, status must be None
            if ($model->DHead === null) {
                $model->dept_head = 'None';
            }
            
            // If DHead exists and status is None, set to Pending
            // (unless it's being manually changed)
            if ($model->DHead !== null && $model->dept_head === 'None' 
                && $model->getOriginal('dept_head') === 'None') {
                $model->dept_head = 'Pending';
            }
        });
    }
    // Default values when creating a new model
    // protected $attributes = [
    //     'dept_head' => 'None', // Default if DHead is null
    // ];

    // protected static function boot()
    // {
    //     parent::boot();

    //     // Only set default status when creating a new record
    //     static::creating(function ($model) {
    //         if ($model->DHead !== null && $model->dept_head === 'None') {
    //             $model->dept_head = 'Pending';
    //         }
    //     });

    //     // Prevent the saving event from overriding manual status changes
    //     // static::saving(function ($model) {
    //     //     // Removed to allow manual status updates
    //     // });
    // }
    
    public function departmentHead()
    {
        return $this->belongsTo(User::class, 'DHead');
    }

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