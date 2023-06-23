<?php

namespace Dystcz\LunarPaypal\Enums;

enum RefundPaymentStatus: string
{
    case CANCELLED = 'CANCELLED';
    case FAILED = 'FAILED';
    case PENDING = 'PENDING';
    case COMPLETED = 'COMPLETED';
}
