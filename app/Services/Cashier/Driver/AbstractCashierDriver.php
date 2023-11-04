<?php

namespace App\Services\Cashier\Driver;

use App\Repositories\TradeLogRepository;
use App\Services\Cashier\DTO\CheckoutData;
use App\Services\Cashier\DTO\CheckoutResponseData;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;

abstract class AbstractCashierDriver implements CashierDriverInterface
{
    protected TradeLogRepository $tradeLogRepo;
    protected ValidationFactory $validationFactory;

    public function __construct(TradeLogRepository $tradeLogRepo, ValidationFactory $validationFactory)
    {
        $this->tradeLogRepo = $tradeLogRepo;
        $this->validationFactory = $validationFactory;
    }

    abstract public function checkout(CheckoutData $data): CheckoutResponseData;
}
