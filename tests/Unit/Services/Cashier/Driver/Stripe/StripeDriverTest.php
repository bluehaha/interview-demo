<?php

namespace Tests\Unit\Services\Cashier\Driver\Stripe;

use App\Models\Price;
use App\Models\Product;
use App\Repositories\TradeLogRepository;
use App\Services\Cashier\Driver\Stripe\StripeDriver;
use App\Services\Cashier\DTO\CheckoutData;
use App\Services\Cashier\DTO\CheckoutResponseData;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Validator;
use Mockery;
use PHPUnit\Framework\TestCase;
use Stripe\Checkout\Session;
use Stripe\Service\Checkout\CheckoutServiceFactory;
use Stripe\Service\Checkout\SessionService;
use Stripe\StripeClient;

class StripeDriverTest extends TestCase
{
    public function test_checkout()
    {
        $price = new Price();
        $price->id = 1;
        $price->product_id = 1;
        $price->stripe_id = 'stripe_price_1';
        $product = new Product();
        $product->id = 1;
        $product->price = $price;

        $validatorMock = Mockery::mock(Validator::class);
        $validatorMock->shouldReceive('fails')->once()->andReturn(false);
        $validationFactoryMock = Mockery::mock(ValidationFactory::class);
        $validationFactoryMock->shouldReceive('make')->once()->andReturn($validatorMock);

        [$stripeClientMock, $session] = $this->prepareStripeClientMock();

        $tradeLogRepoMock = Mockery::mock(TradeLogRepository::class);
        $tradeLogRepoMock->shouldReceive('createFromStripe')->once()->withArgs(function ($data, $resp) {
            return $data instanceof CheckoutData && $resp instanceof CheckoutResponseData;
        });

        Log::shouldReceive('debug')->twice();

        $stripeDriver = new StripeDriver($tradeLogRepoMock, $validationFactoryMock, $stripeClientMock);
        $resp = $stripeDriver->checkout(new CheckoutData([
            'items' => [
                ['product' => $product, 'quantity' => 2],
            ],
            'mode' => 'payment',
        ]));

        $this->assertEquals([
            'isSuccess' => true,
            'errorMessage' => null,
            'type' => 'redirect',
            'url' => $session->url,
            'formParams' => null,
            'payload' => $session->toArray(),
        ], $resp->toArray());
    }

    protected function prepareStripeClientMock()
    {
        $sessionServiceMock = Mockery::mock(SessionService::class);
        $sessionServiceMock->shouldReceive('create')->with([
            'mode' => 'payment',
            'line_items' => [
                [
                    'price' => 'stripe_price_1',
                    'quantity' => 2,
                ],
            ],
        ])->andReturn($session = Session::constructFrom([
            'url' => 'https://checkout.stripe.com/c/pay/...',
        ]));
        $checkoutServiceFactoryMock = Mockery::mock(CheckoutServiceFactory::class);
        $checkoutServiceFactoryMock->shouldReceive('getService')->with('sessions')->andReturn($sessionServiceMock);
        $stripeClientMock = Mockery::mock(StripeClient::class);
        $stripeClientMock->shouldReceive('getService')->with('checkout')->andReturn($checkoutServiceFactoryMock);

        return [$stripeClientMock, $session];
    }

    public function test_checkout_by_getMockBuilder()
    {
        $product = new Product();
        $product->id = 1;

        $tradeLogRepoMock = Mockery::mock(TradeLogRepository::class);
        $tradeLogRepoMock->shouldReceive('createFromStripe')->once()->withArgs(function ($data, $resp) {
            return $data instanceof CheckoutData && $resp instanceof CheckoutResponseData;
        });
        $validationFactoryMock = Mockery::mock(ValidationFactory::class);
        [$stripeClientMock, $session] = $this->prepareStripeClientMock();
        Log::shouldReceive('debug')->twice();

        $stripeDriver = $this->getMockBuilder(StripeDriver::class)
            ->setConstructorArgs([$tradeLogRepoMock, $validationFactoryMock, $stripeClientMock])
            ->setMethods(['validate', 'prepareData'])
            ->getMock();
        $stripeDriver->method('prepareData')->willReturn([
            'mode' => 'payment',
            'line_items' => [
                [
                    'price' => 'stripe_price_1',
                    'quantity' => 2,
                ],
            ],
        ]);

        $resp = $stripeDriver->checkout(new CheckoutData([
            'items' => [
                ['product' => $product, 'quantity' => 2],
            ],
            'mode' => 'payment',
        ]));

        $this->assertEquals([
            'isSuccess' => true,
            'errorMessage' => null,
            'type' => 'redirect',
            'url' => $session->url,
            'formParams' => null,
            'payload' => $session->toArray(),
        ], $resp->toArray());
    }
}
