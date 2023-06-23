<?php

use Dystcz\LunarPaypal\Enums\AuthorizedPaymentStatus;
use Dystcz\LunarPaypal\Enums\OrderStatus;
use Dystcz\LunarPaypal\Facades\PaypalFacade;
use Dystcz\LunarPaypal\PaypalPaymentType;
use Dystcz\LunarPaypal\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Base\DataTransferObjects\PaymentCapture;
use Lunar\Models\Transaction;

uses(TestCase::class, RefreshDatabase::class);

it('works when transaction refers to paypal order', function () {
    /**
     * Since we are not mocking the PayPal client, use a real PayPal order id.
     * Note: the order has to be in the approved state. (use CreateIntentTest.php)
     */
    $paymentIntentId = '3U315607912124635';

    $transaction = Transaction::factory()
        ->create([
            'reference' => $paymentIntentId,
            'amount' => 1000,
            'meta' => [
                'payment_intent_origin' => 'order',
            ],
        ]);

    $payment = new PaypalPaymentType();

    $response = $payment->capture($transaction, 1000);

    expect($response)->toBeInstanceOf(PaymentCapture::class)
        ->and($response->success)->toBeTrue();

    $this->assertDatabaseHas((new Transaction)->getTable(), [
        'parent_transaction_id' => $transaction->id,
        'type' => 'capture',
        'amount' => $transaction->amount,
    ]);

    $paypalOrder = PaypalFacade::fetchOrder($paymentIntentId);

    expect($paypalOrder->status)
        ->toBe(OrderStatus::COMPLETED);
});

it('works when transaction refers to to paypal payment', function () {
    /**
     * Since we are not mocking the PayPal client, use a real PayPal order id.
     * Note: the order has to be in the approved state and created witn lunar.paypal.policy set to manual. (use CreateIntentTest.php)
     */
    $paymentIntentId = '05L40728YN7855211';

    $paypalPaymentId = authorizePayPalOrder($paymentIntentId)->purchase_units[0]->payments->authorizations[0]->id;

    $transaction = Transaction::factory()
        ->create([
            'reference' => $paypalPaymentId,
            'amount' => 1000,
            'meta' => [
                'payment_intent_origin' => 'payment',
            ],
        ]);

    $transaction->order->update(['currency_code' => 'USD']);

    $payment = new PaypalPaymentType();

    $response = $payment->capture($transaction, 1000);

    expect($response)->toBeInstanceOf(PaymentCapture::class)
        ->and($response->success)->toBeTrue();

    $this->assertDatabaseHas((new Transaction)->getTable(), [
        'parent_transaction_id' => $transaction->id,
        'type' => 'capture',
        'amount' => $transaction->amount,
    ]);

    $authorizedPayment = PaypalFacade::getClient()->showAuthorizedPaymentDetails(
        $paypalPaymentId
    );

    expect($authorizedPayment['status'])
        ->toBe(AuthorizedPaymentStatus::CAPTURED->value);
});
