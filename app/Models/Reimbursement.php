<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reimbursement extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'travel_request_id',
        'manager_id',
        'finance_id',
        'approved_by_user_id',
        'category_approver_id',
        'category_approved_at',
        'manager_approved_at',
        'finance_approved_at',
        'pantro_id',
        'pantro_approved_at',
        'tungsen_id',
        'tungsen_approved_at',
        'request_number',
        'category',
        'date',
        'company',
        'note',
        'bank',
        'account_number',
        'transfer_to',
        'total',
        'status',
        'finance_comment',
        'reimbursement_type',
        'reimbursement_status',
        'submitted_at',
        'verified_at',
        'signed_date',
        'reimbursed_at',
        'reimbursed_by',
        'paid_amount',
        'payment_method',
        'transaction_reference',
    ];

    protected $casts = [
        'date' => 'date',
        'signed_date' => 'date',
        'total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'submitted_at' => 'datetime',
        'verified_at' => 'datetime',
        'reimbursed_at' => 'datetime',
        'category_approved_at' => 'datetime',
        'manager_approved_at' => 'datetime',
        'finance_approved_at' => 'datetime',
        'pantro_approved_at' => 'datetime',
        'tungsen_approved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function managerUser()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function finance()
    {
        return $this->belongsTo(User::class, 'finance_id');
    }

    public function categoryApprover()
    {
        return $this->belongsTo(User::class, 'category_approver_id');
    }

    public function pantroUser()
    {
        return $this->belongsTo(User::class, 'pantro_id');
    }

    public function tungsenUser()
    {
        return $this->belongsTo(User::class, 'tungsen_id');
    }

    public function approvedByUser()
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function reimbursedByUser()
    {
        return $this->belongsTo(User::class, 'reimbursed_by');
    }

    public function travelRequest()
    {
        return $this->belongsTo(TravelRequest::class);
    }

    public function items()
    {
        return $this->hasMany(ReimbursementItem::class);
    }

    public function attachments()
    {
        return $this->hasMany(ReimbursementAttachment::class);
    }

    public function approvalHistories()
    {
        return $this->morphMany(ApprovalHistory::class, 'approvable')->latest();
    }
}
