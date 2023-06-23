<?php

namespace Dystcz\LunarPaypal\Data;

use Spatie\LaravelData\Data;

class ProcessorResponse extends Data
{
    public function __construct(
        public readonly string $avs_code,
        public readonly string $cvv_code,
        public readonly string $response_code,
        public readonly string $payment_advice_code
    ) {
    }
}
