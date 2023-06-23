<?php

namespace Dystcz\LunarPaypal\Data;

use Dystcz\LunarPaypal\Contracts\Payment;
use Dystcz\LunarPaypal\Enums\RefundPaymentStatus;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class RefundPayment extends Data implements Payment
{
    public function __construct(
        public readonly string $id,
        public readonly RefundPaymentStatus $status,
        public readonly StatusDetails|Optional $status_details,
        public readonly string|Optional $invoice_id,
        public readonly string|Optional $custom_id,
        public readonly string $note_to_payer,
        public readonly array $links,
        public readonly Amount $amount,
        public readonly Payer|Optional $payer,
        public readonly SellerReceivableBreakdown $seller_payable_breakdown,
        public readonly string $create_time,
        public readonly string $update_time
    ) {
    }
}
