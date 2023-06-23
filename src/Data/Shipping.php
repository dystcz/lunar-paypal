<?php

namespace Dystcz\LunarPaypal\Data;

use Spatie\LaravelData\Data;

class Shipping extends Data
{
    public function __construct(
        public readonly Name $name,
        public readonly Address $address,
    ) {
    }
}
