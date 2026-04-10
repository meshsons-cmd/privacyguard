<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'url',
        'compliance_score',
        'risk_level',
        'summary',
        'missing_clauses',
        'is_paid',
        'razorpay_order_id',
        'razorpay_payment_id',
    ];

    protected $casts = [
        'missing_clauses' => 'array',
        'is_paid' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
