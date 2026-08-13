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
        'reimbursement_type',
        'submitted_at',
        'verified_at',
    ];

    protected $casts = [
        'date' => 'date',
        'total' => 'decimal:2',
        'submitted_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function travelRequest()
    {
        return $this->belongsTo(TravelRequest::class);
    }

    public function items()
    {
        return $this->hasMany(ReimbursementItem::class);
    }
}
