<?php

namespace App\Services\Cashier;

use App\Services\Cashier\Driver\AbstractCashierDriver;
use App\Services\Cashier\Driver\CashierDriverFactory;
use App\Services\Cashier\DTO\CheckoutData;
use App\Services\Cashier\DTO\CheckoutResponseData;
use Illuminate\Support\Facades\Log;
use Throwable;

class CashierService
{
    protected string $type;
    protected CashierDriverFactory $factory;

    public function __construct(CashierDriverFactory $factory)
    {
        $this->factory = $factory;
    }

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
        return $this->factory->create($this->type);
    }
}
