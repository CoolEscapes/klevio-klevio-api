<?php

namespace Klevio\KlevioApi\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Illuminate\Http\Client\Response getKeys()
 * @method static \Illuminate\Http\Client\Response getKey(string $keyId)
 * @method static \Illuminate\Http\Client\Response lock(string $keyId)
 * @method static \Illuminate\Http\Client\Response unlock(string $keyId)
 * 
 * @see \Klevio\KlevioApi\KlevioApi
 */
class KlevioApi extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'klevio-api';
    }
}
