<?php

namespace App\Services\Crypto\Exchanges;

/**
 * Interface FactoryInterface
 * @package App\Services\Crypto\Exchanges
 */
interface FactoryInterface
{
    /**
     * @param string|null $name
     * @param int|null $userId
     * @return ExchangeInterface
     */
    public static function create(?string $name = null, int $userId = null): ExchangeInterface;
}
