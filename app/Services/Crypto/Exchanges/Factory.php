<?php
namespace App\Services\Crypto\Exchanges;

use Illuminate\Support\Str;

/**
 * Class Factory
 * @package App\Services\Crypto\Exchanges
 */
class Factory implements FactoryInterface
{
    /**
     * @param string $name
     * @return FacadeInterface
     */
    public static function create(string $name): FacadeInterface
    {
        $className = 'App\\Services\Crypto\\Exchanges\\'.Str::studly($name).'\\Facade';
        return new $className();
    }
}
