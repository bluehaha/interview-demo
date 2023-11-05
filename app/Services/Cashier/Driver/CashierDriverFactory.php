<?php

namespace App\Services\Cashier\Driver;

use App\Services\Cashier\Driver\Stripe\StripeDriver;
use Illuminate\Support\Facades\App;

class CashierDriverFactory
{
    const DRIVER_MAPPING = [
        'stripe' => StripeDriver::class,
    ];

    protected array $drivers = [];

    public function create(string $type): AbstractCashierDriver
    {
        // 另一種寫法，可以更符合 open close 原則
        // $type = ucfirst($type);
        // $className = __NAMESPACE__ . "\\{$type}\\{$type}Driver";
        // if (class_exists($className))
        //     return App::make($className);
        //
        // throw new \Exception('Driver not found');

        if (isset($this->drivers[$type]))
            return $this->drivers[$type];

        if (isset(self::DRIVER_MAPPING[$type]))
            return $this->drivers[$type] =  App::make(self::DRIVER_MAPPING[$type]);

        throw new \Exception('Driver not found');
    }
}
