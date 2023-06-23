<?php

namespace Dystcz\LunarPaypal\Enums;

enum CapturedPaymentStatus: string
{
    case COMPLETED = 'COMPLETED';
    case DECLINED = 'DECLINED';
    case PARTIALLY_REFUNDED = 'PARTIALLY_REFUNDED';
    case PENDING = 'PENDING';
    case REFUNDED = 'REFUNDED';
    case FAILED = 'FAILED';
}
