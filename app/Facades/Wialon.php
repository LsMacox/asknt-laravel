<?php

namespace App\Facades;

use Adldap\AdldapInterface;
use Illuminate\Support\Facades\Facade;

/**
 * Class Wialon
 * @package App\Facades
 */
class Wialon extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'wialon';
    }
}
