<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TravelRequestDestination extends Model
{
    use HasFactory;

    protected $fillable = [
        'travel_request_id',
        'destination',
        'from',
        'to',
    ];

    public function travelRequest()
    {
        return $this->belongsTo(TravelRequest::class);
    }
}
