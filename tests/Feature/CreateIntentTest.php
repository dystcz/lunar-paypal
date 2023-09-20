<?php

use Dystcz\LunarPaypal\Facades\PaypalFacade;
use Dystcz\LunarPaypal\Tests\TestCase;
use Dystcz\LunarPaypal\Tests\Utils\CartBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(TestCase::class, RefreshDatabase::class);

it('creates new paypal order', function () {
    Config::set('lunar.paypal.policy', true ? 'automatic' : 'manual');

    $cart = CartBuilder::build();

    PaypalFacade::createIntent($cart->calculate());

    expect($cart->refresh()->meta['payment_intent'])->not->toBeNull();
});
