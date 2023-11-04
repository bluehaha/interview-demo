<?php

namespace App\Models;

use Decimal\Decimal;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Price extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'stripe_id',
    ];

    protected $casts = [
        'amount' => 'decimal:10',
    ];

    protected function asDecimal($value, $decimals)
    {
        return new Decimal($value, $decimals);
    }
}
