<?php

use Illuminate\Support\Facades\Route;

Route::post('/stripe', 'StripeController@handleWebhook');
