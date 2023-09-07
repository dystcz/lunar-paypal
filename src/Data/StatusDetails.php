<?php

namespace Dystcz\LunarPaypal\Data;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class StatusDetails extends Data
{
    public function __construct(
        public readonly string|Optional $status,
        public readonly string $reason,
        public readonly string|Optional $note,
        public readonly string|Optional $id
    ) {
    }
}
