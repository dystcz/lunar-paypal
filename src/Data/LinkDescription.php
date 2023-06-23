<?php

namespace Dystcz\LunarPaypal\Data;

use Spatie\LaravelData\Data;

class LinkDescription extends Data
{
    public function __construct(
        public readonly string $href,
        public readonly string $rel,
        public readonly string $method
    ) {
    }
}
