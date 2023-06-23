<?php

namespace Dystcz\LunarPaypal\Data;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class SellerReceivableBreakdown extends Data
{
    public function __construct(
        public readonly array|Optional $platform_fees,
        public readonly Amount $gross_amount,
        public readonly Amount $paypal_fee,
        public readonly Amount|Optional $paypal_fee_in_receivable_currency,
        public readonly Amount $net_amount,
        public readonly Amount|Optional $receivable_amount,
        public readonly ExchangeRate|Optional $exchange_rate
    ) {
    }
}
