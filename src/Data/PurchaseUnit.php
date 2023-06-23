<?php

namespace Dystcz\LunarPaypal\Data;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class PurchaseUnit extends Data
{
    public function __construct(
        public readonly string $reference_id,
        public readonly Amount|Optional $amount,
        public readonly Shipping|Optional $shipping,
        public readonly Payments|Optional $payments,
        public readonly Payee|Optional $payee,
    ) {
    }

    public function totalAmount(): float
    {
        return $this->amount?->value ?? 0;
    }

    public function totalCapturedAmount(): float
    {
        return $this->payments?->captures->reduce(
            fn (CapturedPayment $capturedPayment) => $capturedPayment->amount->value,
            0
        );
    }

    public function payments(): Collection
    {
        $payments = [
            $this->payments?->captures,
            $this->payments?->authorizations,
            $this->payments?->refunds,
        ];

        return collect($payments)
            ->filter(fn ($payment) => ! $payment instanceof Optional)
            ->map(fn ($payment) => $payment->toCollection())
            ->collapse();
    }
}
