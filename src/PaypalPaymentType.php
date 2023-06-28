<?php

namespace Dystcz\LunarPaypal;

use Carbon\Carbon;
use Dystcz\LunarPaypal\Actions\SetPaymentIntentIdOnCart;
use Dystcz\LunarPaypal\Contracts\Payment;
use Dystcz\LunarPaypal\Data\AuthorizedPayment;
use Dystcz\LunarPaypal\Data\CapturedPayment;
use Dystcz\LunarPaypal\Data\Order;
use Dystcz\LunarPaypal\Data\RefundPayment;
use Dystcz\LunarPaypal\Enums\AuthorizedPaymentStatus;
use Dystcz\LunarPaypal\Enums\CapturedPaymentStatus;
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

        $this->paymentIntent = $this->paypal->fetchOrder($this->data['payment_intent']);

        if ($this->policy === 'automatic') {
            $this->paymentIntent = $this->paypal->captureOrder($this->paymentIntent->id);
        } else {
            $this->paymentIntent = $this->paypal->authorizeOrder($this->paymentIntent->id);
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
        try {
            $capturedPayment = $this->paypal->capturePayment($transaction, $amount);
        } catch (InvalidRequestException $e) {
            report($e);

            return new PaymentCapture(
                success: false,
                message: $e->getMessage()
            );
        }

        $transaction->order->transactions()->create([
            'parent_transaction_id' => $transaction->id,
            'success' => ! in_array($capturedPayment->status, CapturedPaymentStatus::failed()),
            'type' => 'capture',
            'driver' => 'paypal',
            'amount' => $amount,
            'reference' => $capturedPayment->id,
            'status' => 'succeeded',
            'notes' => '',
            'captured_at' => now(),
            'card_type' => 'paypal',
        ]);

        return new PaymentCapture(success: true);
    }

    /**
     * Refund a captured transaction
     */
    public function refund(
        Transaction $transaction,
        int $amount = 0,
        $notes = null
    ): PaymentRefund {
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

        $this->createTransaction($refund, 'refund', ['notes' => $notes]);

        return new PaymentRefund(success: true);
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

            foreach ($this->paymentIntent->payments() as $payment) {
                $type = match (get_class($payment)) {
                    AuthorizedPayment::class => 'intent',
                    CapturedPayment::class => 'capture',
                    RefundPayment::class => 'refund',
                    default => null,
                };

                $this->createTransaction($payment, $type);
            }
        });

        return new PaymentAuthorize(success: true);
    }

    protected function createTransaction(
        Payment $payment,
        string $type,
        array $data = []
    ): void {
        $this->order->transactions()->create([
            'success' => ! in_array(
                $payment->status,
                [
                    ...AuthorizedPaymentStatus::failed(),
                    ...CapturedPaymentStatus::failed(),
                    ...RefundPaymentStatus::failed(),
                ]
            ),
            'type' => $type,
            'driver' => 'paypal',
            'amount' => $payment->amount->value,
            'reference' => $payment->id,
            'status' => $payment->status,
            'notes' => '',
            'captured_at' => $type === 'capture' ? Carbon::parse($payment->create_time) : null,
            'card_type' => 'paypal',
            ...$data,
        ]);
    }

    protected function setPaymentIntentIdOnCart(): void
    {
        App::make(SetPaymentIntentIdOnCart::class)($this->cart, $this->paymentIntent->id);
    }

    protected function isReadyToBeReleased(): bool
    {
        return in_array($this->paymentIntent->status, [
            OrderStatus::APPROVED,
            OrderStatus::COMPLETED,
        ]);
    }
}
