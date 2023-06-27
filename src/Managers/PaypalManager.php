<?php

namespace Dystcz\LunarPaypal\Managers;

use Dystcz\LunarPaypal\Actions\SetPaymentIntentIdOnCart;
use Dystcz\LunarPaypal\Concerns\HasReturnAndCancelUrls;
use Dystcz\LunarPaypal\Data\CapturedPayment;
use Dystcz\LunarPaypal\Data\Order as PayPalOrder;
use Dystcz\LunarPaypal\Data\RefundPayment;
use Dystcz\LunarPaypal\Exceptions\InvalidRequestException;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Lunar\Models\Cart;
use Lunar\Models\Transaction;
use Srmklive\PayPal\Services\PayPal;

class PaypalManager
{
    use HasReturnAndCancelUrls;

    /**
     * The policy when capturing payments.
     */
    protected string $policy;

    public function __construct()
    {
        $this->policy = Config::get('lunar.paypal.policy', 'automatic');
    }

    /**
     * Return the PayPal client
     */
    public function getClient(): PayPal
    {
        $provider = new PayPal(Config::get('lunar.paypal'));

        $provider->getAccessToken();

        return $provider;
    }

    /**
     * Create a payment intent from a Cart
     */
    public function createIntent(Cart $cart): PayPalOrder
    {
        $paypalOrder = $this->fetchPayPalOrderFromCart($cart);

        if ($paypalOrder) {
            return $paypalOrder;
        }

        return $this->createPaymentIntent($cart);
    }

    /**
     * Fetch an order from the PayPal API.
     */
    public function fetchOrder(string $orderId): ?PayPalOrder
    {
        try {
            $response = $this->getClient()->showOrderDetails($orderId);

            $this->checkForErrors($response);
        } catch (InvalidRequestException $e) {
            report($e);

            return null;
        }

        return PayPalOrder::from($response);
    }

    /**
     * Capture a payment for an order.
     */
    public function captureOrder(string $orderId): PayPalOrder
    {
        $response = $this->getClient()->capturePaymentOrder($orderId);

        $this->checkForErrors($response);

        return PayPalOrder::from($response);
    }

    /**
     * Authorize a payment for an order.
     */
    public function authorizeOrder(string $orderId): PayPalOrder
    {
        $response = $this->getClient()->authorizePaymentOrder($orderId);

        $this->checkForErrors($response);

        return PayPalOrder::from($response);
    }

    /**
     * Capture a payment for an authorized payment.
     */
    public function capturePayment(Transaction $transaction, float $amount): CapturedPayment
    {
        $client = $this->getClient();

        $client->setCurrency($transaction->order->currency_code);

        $response = $client->captureAuthorizedPayment(
            authorization_id: $transaction->reference,
            invoice_id: $transaction->id,
            amount: $amount,
            note: ''
        );

        $this->checkForErrors($response);

        return CapturedPayment::from(
            $this->getClient()->showCapturedPaymentDetails($response['id'])
        );
    }

    /**
     * Refund a captured payment.
     */
    public function refundPayment(Transaction $transaction, float $amount, string $notes): RefundPayment
    {
        $client = $this->getClient();

        $client->setCurrency($transaction->order->currency_code);

        $response = $client->refundCapturedPayment(
            capture_id: $transaction->reference,
            invoice_id: $transaction->id,
            amount: $amount,
            note: $notes
        );

        $this->checkForErrors($response);

        return RefundPayment::from(
            $this->getClient()->showRefundDetails($response['id'])
        );
    }

    protected function createPayPalOrder(Cart $cart): string
    {
        $shipping = $cart->shippingAddress;

        $data = [
            'intent' => $this->policy === 'automatic' ? 'CAPTURE' : 'AUTHORIZE',
            'purchase_units' => [
                [
                    'amount' => [
                        'currency_code' => $cart->currency->code,
                        'value' => $cart->total->value,
                    ],
                    'shipping' => [
                        'type' => 'SHIPPING',
                        'name' => [
                            'full_name' => $shipping->first_name.' '.$shipping->last_name,
                        ],
                        'address' => [
                            'address_line_1' => $shipping->line_one,
                            'address_line_2' => $shipping->line_two,
                            'admin_area_2' => $shipping->city,
                            'admin_area_1' => $shipping->state,
                            'postal_code' => $shipping->postcode,
                            'country_code' => $shipping->country->iso2,
                        ],
                    ],
                ],
            ],
            'application_context' => [
                'return_url' => $this->getReturnUrl($cart),
                'cancel_url' => $this->getCancelUrl($cart),
            ],
        ];

        $response = $this->getClient()->createOrder($data);

        $this->checkForErrors($response);

        return $response['id'];
    }

    protected function fetchPayPalOrderFromCart(Cart $cart): ?PayPalOrder
    {
        if (! ($cart->meta->payment_intent ?? false)) {
            return null;
        }

        return $this->fetchOrder($cart->meta->payment_intent);
    }

    protected function createPaymentIntent(Cart $cart): PayPalOrder
    {
        $orderId = $this->createPayPalOrder($cart);

        App::make(SetPaymentIntentIdOnCart::class)($cart, $orderId);

        return $this->fetchOrder($orderId);
    }

    protected function checkForErrors(array $response): void
    {
        if (! isset($response['error'])) {
            return;
        }

        $exception = new InvalidRequestException($response['error']);

        throw $exception;
    }
}
