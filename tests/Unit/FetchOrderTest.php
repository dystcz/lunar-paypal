<?php

use Dystcz\LunarPaypal\Data\Order;
use Dystcz\LunarPaypal\Managers\PaypalManager;
use Dystcz\LunarPaypal\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(TestCase::class, RefreshDatabase::class);

it('works', function () {
    $order = app(PaypalManager::class)->fetchOrder('2VP0218213775190R');

    expect($order)->toBeInstanceOf(Order::class);
});
