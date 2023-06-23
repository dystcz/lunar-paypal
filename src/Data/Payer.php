<?php

namespace Dystcz\LunarPaypal\Data;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class Payer extends Data
{
    public function __construct(
        public readonly Name|Optional $name,
        public readonly string $email_address,
        public readonly string|Optional $payer_id,
        public readonly Address|Optional $address,
    ) {
    }
}
