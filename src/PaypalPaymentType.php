<?php

namespace Dystcz\LunarPaypal;

use Dystcz\LunarPaypal\Actions\SetPaymentIntentIdOnCart;
use Dystcz\LunarPaypal\Contracts\Payment;
use Dystcz\LunarPaypal\Data\Amount;
use Dystcz\LunarPaypal\Data\CapturedPayment;
use Dystcz\LunarPaypal\Data\Order;
use Dystcz\LunarPaypal\Enums\OrderStatus;
use Dystcz\LunarPaypal\Enums\RefundPaymentStatus;
use Dystcz\LunarPaypal\Exceptions\InvalidRequestException;
use Dystcz\LunarPaypal\Managers\PaypalManager;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Lunar\Base\DataTransferObjects\PaymentAuthorize;
use Lunar\Base\DataTransferObjects\PaymentCapture;
use Lunar\Base\DataTransferObjects\PaymentRefund;
use Lunar\Models\Transaction;
use Lunar\PaymentTypes\AbstractPayment;
use Spatie\LaravelData\Data;

class PaypalPaymentType extends AbstractPayment
{
    protected PaypalManager $paypal;

    /**
     * PayPal order.
     */
    protected Order $paymentIntent;

    /**
     * The policy when capturing payments.
     */
    protected string $policy;

    public function __construct()
    {
        $this->paypal = new PaypalManager();

        $this->policy = Config::get('lunar.paypal.policy', 'automatic');
    }

    /**
     * Authorize the payment for processing.
     */
    public function authorize(): PaymentAuthorize
    {
        if (! $this->order) {
            if (! $this->order = $this->cart->order) {
                $this->order = $this->cart->createOrder();
            }
        }

        if ($this->order->placed_at) {
            // Somethings gone wrong!
            return new PaymentAuthorize(
                success: false,
                message: 'This order has already been placed',
            );
        }

        $this->paymentIntent = $this->paypal->fetchOrder(
            $this->data['payment_intent']
        );

        if ($this->doesOrderAutomaticCapture()) {
            $this->paymentIntent = $this->paypal->captureOrder(
                $this->data['payment_intent']
            );
        }

        $this->setPaymentIntentIdOnCart();

        if (! $this->isReadyToBeReleased()) {
            return new PaymentAuthorize(
                success: false,
                message: 'Payment not approved',
            );
        }

        return $this->releaseSuccess();
    }

    /**
     * Capture a payment for a transaction.
     */
    public function capture(Transaction $transaction, $amount = 0): PaymentCapture
    {
        $paymentIntentOrigin = $transaction->meta?->payment_intent_origin;

        try {
            if ($paymentIntentOrigin === 'order') {
                // NOTE: Doesn't support amount
                $response = $this->paypal->captureOrder($transaction->reference);
            } else {
                $response = $this->paypal->capturePayment($transaction, $amount);
            }
        } catch (InvalidRequestException $e) {
            report($e);

            return new PaymentCapture(
                success: false,
                message: $e->getMessage()
            );
        }

        $transaction->order->transactions()->create([
            'parent_transaction_id' => $transaction->id,
            'success' => true,
            'type' => 'capture',
            'driver' => 'paypal',
            'amount' => $paymentIntentOrigin === 'order' ? $transaction->amount : $amount,
            'reference' => $response->id,
            'status' => 'succeeded',
            'notes' => '',
            'captured_at' => now(),
            'card_type' => 'paypal',
            'meta' => $transaction->meta,
        ]);

        return new PaymentCapture(success: true);
    }

    /**
     * Refund a captured transaction
     */
    public function refund(Transaction $transaction, int $amount = 0, $notes = null): PaymentRefund
    {
        try {
            $refund = $this->paypal->refundPayment($transaction, $amount, $notes);
        } catch (InvalidRequestException $e) {
            report($e);

            return new PaymentRefund(
                success: false,
                message: $e->getMessage()
            );
        }

        $this->order($transaction->order);

        $this->createTransaction($refund, 'refund', [], [
            'success' => $refund->status !== RefundPaymentStatus::FAILED,
            'notes' => $notes,
        ]);

        return new PaymentRefund(
            success: true
        );
    }

    /**
     * Return a successfully released payment.
     */
    protected function releaseSuccess(): PaymentAuthorize
    {
        DB::transaction(function () {
            $this->order->update([
                'status' => $this->config['released'] ?? 'paid',
                'placed_at' => now(),
            ]);

            $type = $this->policy === 'manual' ? 'intent' : 'capture';

            if ($type === 'capture') {
                foreach ($this->paymentIntent->purchase_units as $unit) {
                    /** @var CapturedPayment $capture */
                    foreach ($unit->payments->captures as $capture) {
                        $this->createTransaction($capture, 'capture');
                    }
                }
            } else {
                $payment = $this->createPaymentFromOrder();

                $this->createTransaction($payment, 'intent', ['payment_intent_origin' => 'order']);
            }
        });

        return new PaymentAuthorize(success: true);
    }

    protected function createTransaction(
        Payment $payment,
        string $type,
        array $meta = [],
        array $data = []
    ): void {
        $this->order->transactions()->create([
            'success' => in_array($payment->status, [OrderStatus::COMPLETED, OrderStatus::APPROVED]),
            'type' => $type,
            'driver' => 'paypal',
            'amount' => $payment->amount->value,
            'reference' => $payment->id,
            'status' => $payment->status,
            'notes' => '',
            'captured_at' => $type === 'capture' ? $payment->create_time : null,
            'card_type' => 'paypal',
            'meta' => [
                'payment_intent_origin' => 'payment',
                ...$meta,
            ],
            ...$data,
        ]);
    }

    protected function setPaymentIntentIdOnCart(): void
    {
        App::make(SetPaymentIntentIdOnCart::class)($this->cart, $this->paymentIntent->id);
    }

    protected function doesOrderAutomaticCapture(): bool
    {
        return $this->paymentIntent->status === OrderStatus::APPROVED
            && $this->policy === 'automatic';
    }

    protected function isReadyToBeReleased(): bool
    {
        return in_array($this->paymentIntent->status, [
            OrderStatus::APPROVED,
            OrderStatus::COMPLETED,
        ]);
    }

    /**
     * PayPal doesn't have a concept of a payment for approved payments,
     * only captured, authorized and rafunds are provided by PayPal.
     * (https://developer.paypal.com/docs/api/orders/v2/#orders_create!c=200&path=purchase_units/payments&t=response)
     * For that reason we need to create a transaction from the order using a fake payment object.
     */
    protected function createPaymentFromOrder(): Payment
    {
        return new class(id: $this->paymentIntent->id, amount: new Amount($this->order->currency_code, $this->paymentIntent->totalAmount()), status: OrderStatus::APPROVED, create_time: now()->toIso8601String()) extends Data implements Payment
        {
            public function __construct(
                public string $id,
                public Amount $amount,
                public OrderStatus $status,
                public string $create_time,
            ) {
            }
        };
    }
}
