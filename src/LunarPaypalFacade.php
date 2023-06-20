<?php

namespace Dystcz\LunarPaypal;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Dystcz\LunarPaypal\Skeleton\SkeletonClass
 */
class LunarPaypalFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'lunar-paypal';
    }
}
