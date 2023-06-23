<?php

namespace Dystcz\LunarPaypal\Enums;

enum SellerProtectionStatus: string
{
    case ELIGIBLE = 'ELIGIBLE';
    case PARTIALLY_ELIGIBLE = 'PARTIALLY_ELIGIBLE';
    case NOT_ELIGIBLE = 'NOT_ELIGIBLE';
}
