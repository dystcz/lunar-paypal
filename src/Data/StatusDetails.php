<?php

namespace Dystcz\LunarPaypal\Data;

use Spatie\LaravelData\Data;

class StatusDetails extends Data
{
    public function __construct(
        public readonly string $status,
        public readonly string $reason,
        public readonly string $note,
        public readonly string $id
    ) {
    }
}
