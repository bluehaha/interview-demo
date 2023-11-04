<?php

namespace Tests\Feature\Services\Cashier\Driver\Stripe;

use App\Models\Price;
use App\Models\Product;
use App\Services\Cashier\Driver\Stripe\StripeDriver;
use App\Services\Cashier\DTO\CheckoutData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\App;
use Mockery;
use Stripe\Checkout\Session;
use Stripe\Service\Checkout\CheckoutServiceFactory;
use Stripe\Service\Checkout\SessionService;
use Stripe\StripeClient;
use Tests\TestCase;

class StripeDriverTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout()
    {
        $product = Product::factory()
            ->has(Price::factory()->count(1), 'price')
            ->create();

        $sessionServiceMock = Mockery::mock(SessionService::class);
        $sessionServiceMock->shouldReceive('create')->with([
            'mode' => 'payment',
            'line_items' => [
                [
                    'price' => $product->price->stripe_id,
                    'quantity' => 2,
                ],
            ],
        ])->andReturn($session = Session::constructFrom([
            'id' => 'stripe_session_id',
            'url' => 'https://checkout.stripe.com/c/pay/...',
        ]));
        $checkoutServiceFactoryMock = Mockery::mock(CheckoutServiceFactory::class);
        $checkoutServiceFactoryMock->shouldReceive('getService')->with('sessions')->andReturn($sessionServiceMock);
        $this->mock(StripeClient::class, function ($mock) use ($checkoutServiceFactoryMock) {
            $mock->shouldReceive('getService')->with('checkout')->once()->andReturn($checkoutServiceFactoryMock);
        });

        $stripeDriver = App::make(StripeDriver::class);
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

        $this->assertDatabaseHas('trade_logs', [
            'vendor' => 'stripe',
            'type' => 'credit_card',
            'status' => 'init',
            'trade_no' => 'stripe_session_id',
            'amount' => $product->price->amount * 2,
            'first_return_info' => json_encode($session->toArray()),
        ]);
    }
}
