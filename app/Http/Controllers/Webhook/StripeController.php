<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Jobs\Webhook\StripeHandlerJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StripeController extends Controller
{
    public function handleWebhook(Request $request)
    {
        @Log::debug('[StripeController@handleWebhook] request', $request->all());

        StripeHandlerJob::dispatch($request->all());

        return response()->json([
            'message' => 'success',
        ]);
    }
}
