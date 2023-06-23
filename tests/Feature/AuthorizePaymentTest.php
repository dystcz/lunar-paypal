<?php

use Dystcz\LunarPaypal\PaypalPaymentType;
use Dystcz\LunarPaypal\Tests\TestCase;
use Dystcz\LunarPaypal\Tests\Utils\CartBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Lunar\Base\DataTransferObjects\PaymentAuthorize;
use Lunar\Models\Transaction;

uses(TestCase::class, RefreshDatabase::class);

// TODO use this to mock the paypal client (somehow) (WIP)
// beforeEach(function () {
//     $this->app->instance(\Srmklive\PayPal\PayPalFacadeAccessor::class, new class extends \Srmklive\PayPal\PayPalFacadeAccessor
//     {
//         /**
//          * Set PayPal API Client to use.
//          *
//          * @return \Srmklive\PayPal\Services\PayPal
//          *
//          * @throws \Exception
//          */
//         public static function setProvider()
//         {
//             $provider = new PayPalClient();
//
//             $mock = new MockHandler([
//                 new Response(200, ['X-Foo' => 'Bar'], 'Hello, World'),
//                 new Response(202, ['Content-Length' => 0]),
//                 new RequestException('Error Communicating with Server', new Request('GET', 'test')),
//             ]);
//
//             $handlerStack = HandlerStack::create($mock);
//             $client = new Client(['handler' => $handlerStack]);
//
//             $provider->setClient($client);
//
//             // Set default provider. Defaults to ExpressCheckout
//             self::$provider = $provider;
//
//             return self::getProvider();
//         }
//     });
// });

it('works when policy is set to automatic', function () {
    /**
     * Since we are not mocking the PayPal client, use a real PayPal order id.
     * Note: the order has to be in the approved state and created witn lunar.paypal.policy set to automatic. (use CreateIntentTest.php)
     */
    $paymentIntentId = '8Y994792DJ983464V';

    $cart = CartBuilder::build([
        'meta' => [
            'payment_intent' => $paymentIntentId,
        ],
    ]);

    $payment = new PaypalPaymentType();

    $response = $payment->cart($cart)->withData([
        'payment_intent' => $cart->meta->payment_intent,
    ])->authorize();

    expect($response)->toBeInstanceOf(PaymentAuthorize::class)
        ->and($response->success)->toBeTrue()
        ->and($cart->refresh()->order->placed_at)->not()->toBeNull();

    $this->assertDatabaseHas((new Transaction)->getTable(), [
        'order_id' => $cart->refresh()->order->id,
        'type' => 'capture',
        'meta' => json_encode(['payment_intent_origin' => 'payment']),
    ]);
});

it('works when policy is set to manual', function () {
    /**
     * Since we are not mocking the PayPal client, use a real PayPal order id.
     * Note: the order has to be in the approved state and created witn lunar.paypal.policy set to manual. (use CreateIntentTest.php)
     */
    $paymentIntentId = '2D132449EH2538442';

    Config::set('lunar.paypal.policy', 'manual');

    $cart = CartBuilder::build([
        'meta' => [
            'payment_intent' => $paymentIntentId,
        ],
    ]);

    $payment = new PaypalPaymentType();

    $response = $payment->cart($cart)->withData([
        'payment_intent' => $cart->meta->payment_intent,
    ])->authorize();

    expect($response)->toBeInstanceOf(PaymentAuthorize::class)
        ->and($response->success)->toBeTrue()
        ->and($cart->refresh()->order->placed_at)->not()->toBeNull();

    $this->assertDatabaseHas((new Transaction)->getTable(), [
        'order_id' => $cart->refresh()->order->id,
        'type' => 'intent',
        'meta' => json_encode(['payment_intent_origin' => 'order']),
    ]);
});
