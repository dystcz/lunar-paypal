<?php

namespace Dystcz\LunarPaypal\Data;

use Spatie\LaravelData\Data;

class Payee extends Data
{
    public function __construct(
        public readonly string $email_address,
        public readonly string $merchant_id,
    ) {
    }
}
