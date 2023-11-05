<?php

namespace Tests\Unit\Services\Cashier\Driver;

use App\Services\Cashier\Driver\CashierDriverFactory;
use App\Services\Cashier\Driver\Stripe\StripeDriver;
use Illuminate\Support\Facades\App;
use Mockery;
use PHPUnit\Framework\TestCase;

class CashierDriverFactoryTest extends TestCase
{
    public function test_create()
    {
        $mock = Mockery::mock(StripeDriver::class);
        App::shouldReceive('make')->once()->with(StripeDriver::class)->andReturn($mock);

        $factory = new CashierDriverFactory();

        $driver = $factory->create('stripe');

        $this->assertInstanceOf(StripeDriver::class, $driver);
    }
}
