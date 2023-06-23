<?php

namespace Dystcz\LunarPaypal\Data;

use Spatie\LaravelData\Data;

class Amount extends Data
{
    public function __construct(
        public readonly string $currency_code,
        public readonly string $value
    ) {
    }
}
