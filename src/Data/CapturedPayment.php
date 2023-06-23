<?php

namespace Dystcz\LunarPaypal\Data;

use Dystcz\LunarPaypal\Contracts\Payment;
use Dystcz\LunarPaypal\Enums\CapturedPaymentStatus;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Optional;

class CapturedPayment extends Data implements Payment
{
    public function __construct(
        public readonly string $id,
        public readonly CapturedPaymentStatus $status,
        public readonly StatusDetails|Optional $status_details,
        public readonly string|Optional $invoice_id,
        public readonly string|Optional $custom_id,
        public readonly bool $final_capture,
        public readonly string|Optional $disbursement_mode,
        #[DataCollectionOf(Link::class)]
        public readonly DataCollection $links,
        public readonly Amount $amount,
        public readonly SellerProtection $seller_protection,
        public readonly SellerReceivableBreakdown $seller_receivable_breakdown,
        public readonly ProcessorResponse|Optional $processor_response,
        public readonly string $create_time,
        public readonly string $update_time
    ) {
    }
}
