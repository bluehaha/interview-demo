<?php

namespace App\Repositories;

use App\Models\TradeLog;
use App\Services\Cashier\DTO\CheckoutData;
use App\Services\Cashier\DTO\CheckoutResponseData;

class TradeLogRepository
{
    protected TradeLog $tradeLog;

    public function __construct(TradeLog $tradeLog)
    {
        $this->tradeLog = $tradeLog;
    }

    public function createFromStripe(CheckoutData $data, CheckoutResponseData $respData): TradeLog
    {
        $amount = 0;
        foreach ($data->items as $item) {
            $amount += $item->product->price->amount * $item->quantity;
        }

        return TradeLog::create([
            'vendor' => TradeLog::VENDOR_STRIPE,
            'type' => TradeLog::TYPE_CREDIT_CARD,
            'status' => TradeLog::STATUS_INIT,
            'trade_no' => $respData->payload['id'],
            'amount' => $amount,
            'first_return_info' => $respData->payload,
        ]);
    }
}
