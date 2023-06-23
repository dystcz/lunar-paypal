<?php

use Dystcz\LunarPaypal\Enums\RefundPaymentStatus;
use Dystcz\LunarPaypal\Facades\PaypalFacade;
use Dystcz\LunarPaypal\PaypalPaymentType;
use Dystcz\LunarPaypal\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Base\DataTransferObjects\PaymentRefund;
use Lunar\Models\Transaction;

uses(TestCase::class, RefreshDatabase::class);

it('works', function () {
    /**
     * Since we are not mocking the PayPal client, use a real PayPal order id.
     * Note: the order has to be in the approved state and created witn lunar.paypal.policy set to automatic. (use CreateIntentTest.php)
     */
    $paymentIntentId = '4RE15348XW1356143';

    $paypalPaymentId = capturePayPalOrder($paymentIntentId)->purchase_units[0]->payments->captures[0]->id;

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

    $response = $payment->refund($transaction, 1000, 'Refund test');

    expect($response)->toBeInstanceOf(PaymentRefund::class)
        ->and($response->success)->toBeTrue();

    $this->assertDatabaseHas((new Transaction)->getTable(), [
        'type' => 'refund',
        'amount' => $transaction->amount,
    ]);

    $refundPayment = PaypalFacade::getClient()
        ->showRefundDetails($transaction->order->transactions->last()->reference);

    expect($refundPayment['status'])
        ->toBe(RefundPaymentStatus::COMPLETED->value);
});
