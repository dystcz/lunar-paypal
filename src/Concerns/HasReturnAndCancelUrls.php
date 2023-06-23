<?php

namespace Dystcz\LunarPaypal\Concerns;

use Illuminate\Support\Facades\Config;
use Lunar\Models\Cart;

trait HasReturnAndCancelUrls
{
    protected static $returnUrlCallback;

    protected static $cancelUrlCallback;

    public static function returnUrlCallback(?callable $callback): void
    {
        static::$returnUrlCallback = $callback;
    }

    public static function cancelUrlCallback(?callable $callback): void
    {
        static::$cancelUrlCallback = $callback;
    }

    public static function getReturnUrl(Cart $cart): string
    {
        if (! static::$returnUrlCallback) {
            return Config::get('lunar.paypal.return_url');
        }

        return call_user_func(static::$returnUrlCallback, $cart);
    }

    public static function getCancelUrl(Cart $cart): string
    {
        if (! static::$cancelUrlCallback) {
            return Config::get('lunar.paypal.cancel_url');
        }

        return call_user_func(static::$cancelUrlCallback, $cart);
    }
}
