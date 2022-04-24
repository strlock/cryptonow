<?php
namespace App\Crypto\Exchanges;

use Illuminate\Support\Str;

/**
 * Class Factory
 * @package App\Crypto\Exchanges
 */
class Factory implements FactoryInterface
{
    /**
     * @param string $name
     * @return FacadeInterface
     */
    public static function create(string $name): FacadeInterface
    {
        $className = 'App\\Crypto\\Exchanges\\'.Str::studly($name).'\\Facade';
        return new $className();
    }
}
