<?php

namespace Klevio\KlevioApi;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Klevio\KlevioApi\Skeleton\SkeletonClass
 */
class KlevioApiFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'klevio-api';
    }
}
