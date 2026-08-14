<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TravelRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'manager_id',
        'approved_by_user_id',
        'category_approver_id',
        'category_approved_at',
        'manager_approved_at',
        'pantro_id',
        'pantro_approved_at',
        'request_number',
        'category',
        'date',
        'company',
        'justification',
        'benefit',
        'supporting_invitation',
        'supporting_custom',
        'supporting_label_1',
        'supporting_label_2',
        'supporting_label_3',
        'supporting_label_4',
        'supporting_value_1',
        'supporting_value_2',
        'supporting_value_3',
        'supporting_value_4',
        'status',
        'manager_comment',
        'submitted_at',
        'approved_at',
        'signed_date',
    ];

    protected $casts = [
        'date' => 'date',
        'signed_date' => 'date',
        'supporting_invitation' => 'boolean',
        'supporting_custom' => 'boolean',
        'supporting_value_1' => 'boolean',
        'supporting_value_2' => 'boolean',
        'supporting_value_3' => 'boolean',
        'supporting_value_4' => 'boolean',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'category_approved_at' => 'datetime',
        'manager_approved_at' => 'datetime',
        'pantro_approved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function categoryApprover()
    {
        return $this->belongsTo(User::class, 'category_approver_id');
    }

    public function pantroUser()
    {
        return $this->belongsTo(User::class, 'pantro_id');
    }

    public function approvedByUser()
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function destinations()
    {
        return $this->hasMany(TravelRequestDestination::class);
    }

    public function reimbursement()
    {
        return $this->hasOne(Reimbursement::class);
    }

    public function approvalHistories()
    {
        return $this->morphMany(ApprovalHistory::class, 'approvable')->latest();
    }
}
