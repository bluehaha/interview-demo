<?php

namespace Tests\Unit\Services\Cashier;

use App\Models\Product;
use App\Services\Cashier\CashierService;
use App\Services\Cashier\Driver\Stripe\StripeDriver;
use App\Services\Cashier\DTO\CheckoutData;
use App\Services\Cashier\DTO\CheckoutResponseData;
use Illuminate\Support\Facades\App;
use Mockery;
use PHPUnit\Framework\TestCase;

class CashierServiceTest extends TestCase
{
    public function test_checkout()
    {
        $mock = Mockery::mock(StripeDriver::class);
        $mock->shouldReceive('checkout')->once()->andReturn(new CheckoutResponseData([
            'isSuccess' => true,
            'type' => 'redirect',
            'payload' => [],
        ]));

        App::shouldReceive('make')->once()->with(StripeDriver::class)->andReturn($mock);

        $service = new CashierService();
        $resp = $service->setType('stripe')->checkout(new CheckoutData([
            'items' => [
                ['product' => new Product(), 'quantity' => 1],
            ],
            'mode' => 'payment',
        ]));

        $this->assertInstanceOf(CheckoutResponseData::class, $resp);
    }

    // 通常 protected, private function 不需要測試，但如果該函式邏輯複雜，還是可為其撰寫測試
    // 這邊只是示範如何測試 protected function
    public function test_create_driver()
    {
        $mock = Mockery::mock(StripeDriver::class);
        App::shouldReceive('make')->once()->with(StripeDriver::class)->andReturn($mock);

        $service = new CashierService();
        $service->setType('stripe');

        $reflect = new \ReflectionClass($service);
        $method = $reflect->getMethod('createDriver');
        $method->setAccessible(true);
        $driver = $method->invokeArgs($service, []);

        $this->assertInstanceOf(StripeDriver::class, $driver);
    }
}
