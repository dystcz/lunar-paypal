<?php

namespace Dystcz\LunarPaypal\Data;

use Spatie\LaravelData\Data;

class PlatformFee extends Data
{
    public function __construct(
        public readonly Amount $gross_amount,
        public readonly Amount $paypal_fee,
        public readonly Amount $paypal_fee_in_receivable_currency,
        public readonly Amount $net_amount,
        public readonly Amount $receivable_amount,
        public readonly ExchangeRate $exchange_rate
    ) {
    }
}
