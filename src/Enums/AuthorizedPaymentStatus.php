<?php

namespace Dystcz\LunarPaypal\Enums;

enum AuthorizedPaymentStatus: string
{
    case CREATED = 'CREATED';
    case CAPTURED = 'CAPTURED';
    case DENIED = 'DENIED';
    case PARTIALLY_CAPTURED = 'PARTIALLY_CAPTURED';
    case VOIDED = 'VOIDED';
    case PENDING = 'PENDING';
}
