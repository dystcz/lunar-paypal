<?php

namespace Dystcz\LunarPaypal\Data;

use Spatie\LaravelData\Data;

class SellerProtection extends Data
{
    public function __construct(
        public readonly string $status,
        public readonly array $dispute_categories
    ) {
    }
}
