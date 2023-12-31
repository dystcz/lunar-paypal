<?php

namespace Dystcz\LunarPaypal\Tests\Utils;

use Lunar\DataTypes\Price;
use Lunar\DataTypes\ShippingOption;
use Lunar\Facades\ShippingManifest;
use Lunar\Models\Cart;
use Lunar\Models\CartAddress;
use Lunar\Models\CartLine;
use Lunar\Models\Country;
use Lunar\Models\Currency;
use Lunar\Models\Language;
use Lunar\Models\ProductVariant;
use Lunar\Models\TaxClass;

class CartBuilder
{
    public static function build(array $cartParams = []): Cart
    {
        Language::factory()->create([
            'default' => true,
        ]);

        $currency = Currency::first();

        $taxClass = TaxClass::factory()->create();

        $cart = Cart::factory()->create(
            array_merge([
                'currency_id' => $currency->id,
            ], $cartParams)
        );

        ShippingManifest::addOption(
            new ShippingOption(
                name: 'Basic Delivery',
                description: 'Basic Delivery',
                identifier: 'BASDEL',
                price: new Price(500, $cart->currency, 1),
                taxClass: $taxClass
            )
        );

        CartAddress::factory()->create([
            'cart_id' => $cart->id,
            'shipping_option' => 'BASDEL',
            'country_id' => Country::factory()->state(['iso2' => 'GB', 'iso3' => 'GBR']),
        ]);

        CartAddress::factory()->create([
            'cart_id' => $cart->id,
            'type' => 'billing',
        ]);

        ProductVariant::factory()->create()->each(function ($variant) use ($cart, $currency) {
            $variant->prices()->create([
                'price' => 1.99,
                'currency_id' => $currency->id,
            ]);

            CartLine::factory()->create([
                'cart_id' => $cart->id,
                'purchasable_type' => ProductVariant::class,
                'purchasable_id' => $variant->id,
            ]);
        });


        return $cart;
    }
}
