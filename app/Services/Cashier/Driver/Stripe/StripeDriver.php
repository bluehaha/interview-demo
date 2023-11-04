<?php

namespace App\Services\Cashier\Driver\Stripe;

use App\Repositories\TradeLogRepository;
use App\Services\Cashier\Driver\AbstractCashierDriver;
use App\Services\Cashier\DTO\CheckoutData;
use App\Services\Cashier\DTO\CheckoutItemCollection;
use App\Services\Cashier\DTO\CheckoutResponseData;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;

class StripeDriver extends AbstractCashierDriver
{
    protected StripeClient $stripeClient;

    public function __construct(
        TradeLogRepository $tradeLogRepo,
        ValidationFactory $validationFactory,
        StripeClient $stripeClient
    ) {
        $this->stripeClient = $stripeClient;
        parent::__construct($tradeLogRepo, $validationFactory);
    }

    public function checkout(CheckoutData $data): CheckoutResponseData
    {
        $this->validate($data);
        $stripeData = $this->prepareData($data);

        @Log::debug('[StripeDriver@checkout] checkout data', ['data' => $stripeData]);

        $session = $this->stripeClient->checkout->sessions->create($stripeData);

        @Log::debug('[StripeDriver@checkout] resp', ['resp' => $session]);

        $resp = new CheckoutResponseData([
            'isSuccess' => true,
            'type' => 'redirect',
            'url' => $session->url,
            'payload' => $session->toArray(),
        ]);

        $this->tradeLogRepo->createFromStripe($data, $resp);

        return $resp;
    }

    protected function validate(CheckoutData $data): void
    {
        $validator = $this->validationFactory->make(
            $data->toArray(),
            [
                'items' => 'required|array',
                'items.*.product' => 'required',
                'items.*.quantity' => 'required|integer',
                'mode' => 'required|string',
                'cancleUrl' => 'nullable|url',
                'successUrl' => 'nullable|url',
                'currency' => 'nullable|string',
                'customerOuterCode' => 'nullable|string',
                'customerEmail' => 'nullable|email',
            ],
        );

        if ($validator->fails()) {
            throw new \Exception($validator->errors());
        }
     }

    protected function prepareData(CheckoutData $data): array
    {
        $data = [
            'mode' => $data->mode,
            'line_items' => $this->prepareLineItems($data->items),
            'success_url' => $data->successUrl,
            'cancel_url' => $data->cancleUrl,
            'currency' => $data->currency,
            'customer' => $data->customerOuterCode,
            'customer_email' => $data->customerEmail,
        ];
        return array_filter($data);
    }

    protected function prepareLineItems(CheckoutItemCollection $items): array
    {
        $lineItems = [];
        foreach ($items as $item) {
            $lineItems[] = [
                'price' => $item->product->price->stripe_id,
                'quantity' => $item->quantity,
            ];
        }

        return $lineItems;
    }
 }
