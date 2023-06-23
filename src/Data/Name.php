<?php

namespace Dystcz\LunarPaypal\Data;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class Name extends Data
{
    public function __construct(
        public readonly string|Optional $given_name,
        public readonly string|Optional $surname,
        public readonly string|Optional $full_name
    ) {
    }
}
