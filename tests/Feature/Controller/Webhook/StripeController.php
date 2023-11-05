<?php

namespace Tests\Feature\Controller\Webhook;

use App\Jobs\Webhook\StripeHandlerJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class StripeController extends TestCase
{
    public function test_handleWebhook()
    {
        Bus::fake();

        $response = $this->post('webhook/stripe', [
            'foo' => 'bar',
        ]);

        $response->assertStatus(200);

        Bus::assertDispatched(StripeHandlerJob::class, function ($job) {
            return $job->payload === [
                'foo' => 'bar',
            ];
        });
    }
}
