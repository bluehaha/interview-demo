<?php

namespace App\Services\Cashier\Driver;

use App\Services\Cashier\DTO\CheckoutData;
use App\Services\Cashier\DTO\CheckoutResponseData;

interface CashierDriverInterface
{
    public function checkout(CheckoutData $data): CheckoutResponseData;
}
