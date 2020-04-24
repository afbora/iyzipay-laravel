<?php

namespace Afbora\IyzipayLaravel;

use Illuminate\Support\Facades\Facade;

class IyzipayLaravelFacade extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'iyzipay-laravel';
    }
}
