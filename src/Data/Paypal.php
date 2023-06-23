<?php

namespace Dystcz\LunarPaypal\Data;

use Spatie\LaravelData\Data;

class Paypal extends Data
{
    public function __construct(
        public readonly Name $name,
        public readonly string $email_address,
        public readonly string $account_id,
        public readonly string $account_status,
        public readonly Address $address,
    ) {
    }
}
