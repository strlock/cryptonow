<?php

namespace App\Services\Crypto\Exchanges;

/**
 * Interface FactoryInterface
 * @package App\Services\Crypto\Exchanges
 */
interface FactoryInterface
{
    /**
     * @param string $name
     * @return FacadeInterface
     */
    public static function create(string $name): FacadeInterface;
}
