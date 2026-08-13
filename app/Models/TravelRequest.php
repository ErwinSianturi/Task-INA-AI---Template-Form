<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TravelRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
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
        'submitted_at',
        'approved_at',
    ];

    protected $casts = [
        'date' => 'date',
        'supporting_invitation' => 'boolean',
        'supporting_custom' => 'boolean',
        'supporting_value_1' => 'boolean',
        'supporting_value_2' => 'boolean',
        'supporting_value_3' => 'boolean',
        'supporting_value_4' => 'boolean',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function destinations()
    {
        return $this->hasMany(TravelRequestDestination::class);
    }

    public function reimbursement()
    {
        return $this->hasOne(Reimbursement::class);
    }
}
