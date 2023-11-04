<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TradeLog extends Model
{
    const VENDOR_STRIPE = 'stripe';

    const TYPE_CREDIT_CARD = 'credit_card';

    const STATUS_INIT = 'init';

    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'paid_at' => 'timestamp',
        'first_return_info' => 'json',
        'return_info' => 'json',
    ];
}
