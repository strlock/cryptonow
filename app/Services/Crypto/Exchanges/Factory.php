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
     * @param string|null $name
     * @param int|null $userId
     * @return FacadeInterface
     */
    public static function create(?string $name = null, int $userId = null): FacadeInterface
    {
        if (empty($name)) {
            $name = config('crypto.defaultExchange');
        }
        $className = 'App\\Services\Crypto\\Exchanges\\'.Str::studly($name).'\\Facade';
        return new $className($userId);
    }
}
