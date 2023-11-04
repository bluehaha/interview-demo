<?php

namespace App\Services\Cashier;

use App\Services\Cashier\Driver\AbstractCashierDriver;
use App\Services\Cashier\Driver\Stripe\StripeDriver;
use App\Services\Cashier\DTO\CheckoutData;
use App\Services\Cashier\DTO\CheckoutResponseData;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Throwable;

class CashierService
{
    const DRIVER_MAPPING = [
        'stripe' => StripeDriver::class,
    ];

    protected string $type;
    protected array $drivers;

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function checkout(CheckoutData $data): CheckoutResponseData
    {
        try {
            return $this->createDriver()->checkout($data);
        } catch (Throwable $e) {
            @Log::error('[CashierService@checkout] checkout error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            // 此處可以根據業務需要，再決定要回傳什麼錯誤訊息
            throw $e;
        }
    }

    protected function createDriver(): AbstractCashierDriver
    {
        if (isset($this->drivers[$this->type]))
            return $this->drivers[$this->type];

        if (isset(self::DRIVER_MAPPING[$this->type]))
            return $this->drivers[$this->type] = App::make(self::DRIVER_MAPPING[$this->type]);

        throw new \Exception('Driver not found');
    }
}
