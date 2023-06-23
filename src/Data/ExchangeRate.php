<?php

namespace Dystcz\LunarPaypal\Data;

use Spatie\LaravelData\Data;

class ExchangeRate extends Data
{
    public function __construct(
        public readonly string $source_currency,
        public readonly string $target_currency,
        public readonly string $value
    ) {
    }
}
