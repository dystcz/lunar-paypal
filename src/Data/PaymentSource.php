<?php

namespace Dystcz\LunarPaypal\Data;

use Spatie\LaravelData\Data;

class PaymentSource extends Data
{
    public function __construct(
        public readonly Paypal $paypal,
    ) {
    }
}
