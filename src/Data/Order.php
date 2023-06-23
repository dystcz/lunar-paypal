<?php

namespace Dystcz\LunarPaypal\Data;

use Dystcz\LunarPaypal\Enums\OrderStatus;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Optional;

class Order extends Data
{
    public function __construct(
        public readonly string $id,
        public readonly OrderStatus|Optional $status,
        public readonly string|Optional $intent,
        public readonly PaymentSource|Optional $payment_source,
        #[DataCollectionOf(PurchaseUnit::class)]
        public readonly DataCollection|Optional $purchase_units,
        public readonly Payer|Optional $payer,
        public readonly string|Optional $create_time,
        public readonly string|Optional $update_time,
        #[DataCollectionOf(Link::class)]
        public readonly DataCollection|Optional $links,
    ) {
    }

    public function totalAmount(): float
    {
        return $this->purchase_units->reduce(fn (int $i, PurchaseUnit $purchaseUnit) => $purchaseUnit->totalAmount(),
            0);
    }

    public function totalCapturedAmount(): float
    {
        return $this->purchase_units->reduce(fn (PurchaseUnit $purchaseUnit) => $purchaseUnit->totalCapturedAmount(),
            0);
    }
}
