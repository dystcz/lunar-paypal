<?php

namespace Dystcz\LunarPaypal\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Dystcz\LunarPaypal\Skeleton\SkeletonClass
 */
class PaypalFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'gc:paypal';
    }
}
