<?php

use Dystcz\LunarPaypal\Actions\VerifyWebhookSignature;
use Dystcz\LunarPaypal\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(TestCase::class, RefreshDatabase::class);

it('works', function () {
    $data = json_decode(file_get_contents(__DIR__.'/../Stubs/Webhooks/order.approved.json'), true);

    App::make(VerifyWebhookSignature::class)($data['body'], $data['headers']);
})
    ->skip('Works only if the webhook can be found in Event Logs -> Webhook Events in PayPal dashboard');
