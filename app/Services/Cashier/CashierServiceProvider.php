<?php

namespace App\Services\Cashier;

use Illuminate\Support\ServiceProvider;
use Stripe\StripeClient;

class CashierServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(StripeClient::class, function () {
            return new StripeClient(config('cashier.stripe.secret_key'));
        });
    }

    public function boot()
    {
    }
}
