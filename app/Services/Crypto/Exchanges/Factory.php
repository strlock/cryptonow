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
     * @return ExchangeInterface
     */
    public static function create(?string $name = null, int $userId = null): ExchangeInterface
    {
        if (empty($name)) {
            $name = config('crypto.defaultExchange');
        }
        $className = 'App\\Services\Crypto\\Exchanges\\'.Str::studly($name).'\\Exchange';
        return new $className($userId);
    }
}
