<?php

use Dystcz\LunarPaypal\Data\Order;
use Dystcz\LunarPaypal\Exceptions\InvalidRequestException;
use Dystcz\LunarPaypal\Managers\PaypalManager;

function authorizePayPalOrder(string $orderId): Order
{
    $response = app(PaypalManager::class)
        ->getClient()
        ->authorizePaymentOrder($orderId);

    if (isset($response['error'])) {
        throw new InvalidRequestException($response['error']);
    }

    return app(PaypalManager::class)
        ->fetchOrder($orderId);
}

function capturePayPalOrder(string $orderId): Order
{
    $response = app(PaypalManager::class)
        ->getClient()
        ->capturePaymentOrder($orderId);

    if (isset($response['error'])) {
        throw new InvalidRequestException($response['error']);
    }

    return app(PaypalManager::class)
        ->fetchOrder($orderId);
}
