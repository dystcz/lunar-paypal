<?php

use Dystcz\LunarPaypal\Managers\PaypalManager;
use Dystcz\LunarPaypal\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Lunar\Models\Cart;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    // Reset the trait's state before each test.
    PaypalManager::returnUrlCallback(null);
    PaypalManager::cancelUrlCallback(null);
});

it('sets and retrieves return url via callback', function () {
    $cart = new Cart();

    PaypalManager::returnUrlCallback(function ($cart) {
        return 'http://return.com';
    });

    expect(PaypalManager::getReturnUrl($cart))->toBe('http://return.com');
});

it('sets and retrieves cancel url via callback', function () {
    $cart = new Cart();

    PaypalManager::cancelUrlCallback(function ($cart) {
        return 'http://cancel.com';
    });

    expect(PaypalManager::getCancelUrl($cart))->toBe('http://cancel.com');
});

it('returns default return url if no callback is set', function () {
    $cart = new Cart();

    // Set the value in the config for this test
    Config::set('lunar.paypal.return_url', 'http://defaultreturn.com');

    expect(PaypalManager::getReturnUrl($cart))->toBe('http://defaultreturn.com');
});

it('returns default cancel url if no callback is set', function () {
    $cart = new Cart();

    // Set the value in the config for this test
    Config::set('lunar.paypal.cancel_url', 'http://defaultcancel.com');

    expect(PaypalManager::getCancelUrl($cart))->toBe('http://defaultcancel.com');
});
