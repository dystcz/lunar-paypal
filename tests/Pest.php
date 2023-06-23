<?php

use Dystcz\LunarPaypal\Data\Order;
use Dystcz\LunarPaypal\Enums\OrderStatus;
use Dystcz\LunarPaypal\Exceptions\InvalidRequestException;
use Dystcz\LunarPaypal\Managers\PaypalManager;

function authorizePayPalOrder(string $orderId): Order
{
    $paypalOrder = app(PaypalManager::class)->fetchOrder($orderId);

    if ($paypalOrder->intent !== 'AUTHORIZE') {
        throw new RuntimeException('Order\'s intent has to be set to AUTHORIZE.');
    }

    if ($paypalOrder->status === OrderStatus::COMPLETED) {
        return $paypalOrder;
    }

    $response = app(PaypalManager::class)
        ->getClient()
        ->authorizePaymentOrder($orderId);

    if (isset($response['error'])) {
        throw new InvalidRequestException($response['error']);
    }

    return app(PaypalManager::class)->fetchOrder($orderId);
}

function capturePayPalOrder(string $orderId): Order
{
    $paypalOrder = app(PaypalManager::class)->fetchOrder($orderId);

    if ($paypalOrder->intent !== 'CAPTURE') {
        throw new RuntimeException('Order\'s intent has to be set to CAPTURE.');
    }

    if ($paypalOrder->status === OrderStatus::COMPLETED) {
        return $paypalOrder;
    }

    $response = app(PaypalManager::class)
        ->getClient()
        ->capturePaymentOrder($orderId);

    if (isset($response['error'])) {
        throw new InvalidRequestException($response['error']);
    }

    return app(PaypalManager::class)
        ->fetchOrder($orderId);
}
