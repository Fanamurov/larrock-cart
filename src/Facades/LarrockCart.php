<?php

namespace Larrock\ComponentCart\Facades;

use Illuminate\Support\Facades\Facade;

class LarrockCart extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'larrockcart';
    }

}