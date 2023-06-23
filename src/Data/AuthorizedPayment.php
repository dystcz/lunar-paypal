<?php

namespace Dystcz\LunarPaypal\Data;

use Dystcz\LunarPaypal\Contracts\Payment;
use Dystcz\LunarPaypal\Enums\AuthorizedPaymentStatus;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Optional;

class AuthorizedPayment extends Data implements Payment
{
    public function __construct(
        public readonly AuthorizedPaymentStatus $status,
        public readonly StatusDetails|Optional $status_details,
        public readonly string $id,
        public readonly string|Optional $invoice_id,
        public readonly string|Optional $custom_id,
        #[DataCollectionOf(Link::class)]
        public readonly DataCollection $links,
        public readonly Amount $amount,
        public readonly SellerProtection $seller_protection,
        public readonly string $expiration_time,
        public readonly string $create_time,
        public readonly string $update_time,
        public readonly ProcessorResponse|Optional $processor_response
    ) {
    }
}
