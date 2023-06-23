<?php

namespace Dystcz\LunarPaypal\Data;

use Spatie\LaravelData\Data;

class SellerPayableBreakdown extends Data
{
    public function __construct(
        public readonly array $platform_fees,
        public readonly array $net_amount_breakdown,
        public readonly Amount $gross_amount,
        public readonly Amount $paypal_fee,
        public readonly Amount $paypal_fee_in_receivable_currency,
        public readonly Amount $net_amount,
        public readonly Amount $receivable_amount,
        public readonly Amount $net_amount_in_receivable_currency,
    ) {
    }
}
