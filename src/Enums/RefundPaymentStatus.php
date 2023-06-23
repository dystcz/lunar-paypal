<?php

namespace Dystcz\LunarPaypal\Enums;

enum RefundPaymentStatus: string
{
    case CANCELLED = 'CANCELLED';
    case FAILED = 'FAILED';
    case PENDING = 'PENDING';
    case COMPLETED = 'COMPLETED';

    public static function failed(): array
    {
        return [
            self::CANCELLED,
            self::FAILED,
        ];
    }
}
