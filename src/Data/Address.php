<?php

namespace Dystcz\LunarPaypal\Data;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class Address extends Data
{
    public function __construct(
        public readonly Optional|string $address_line_1,
        public readonly Optional|string $admin_area_2,
        public readonly Optional|string $admin_area_1,
        public readonly Optional|string $postal_code,
        public readonly string $country_code,
    ) {
    }
}
