<?php

namespace Dystcz\LunarPaypal\Data;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Optional;

class Payments extends Data
{
    public function __construct(
        #[DataCollectionOf(AuthorizedPayment::class)]
        public readonly DataCollection|Optional $authorizations,
        #[DataCollectionOf(CapturedPayment::class)]
        public readonly DataCollection|Optional $captures,
        #[DataCollectionOf(RefundPayment::class)]
        public readonly DataCollection|Optional $refunds,
    ) {
    }
}
