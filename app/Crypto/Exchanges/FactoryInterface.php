<?php

namespace App\Crypto\Exchanges;

/**
 * Interface FactoryInterface
 * @package App\Crypto\Exchanges
 */
interface FactoryInterface
{
    /**
     * @param string $name
     * @return FacadeInterface
     */
    public static function create(string $name): FacadeInterface;
}
