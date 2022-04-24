<?php
namespace App\Crypto\Exchanges;

/**
 * Interface TradeInterface
 * @package App\Crypto\Exchanges
 */
interface TradeInterface
{
    /**
     * @return int
     */
    public function getId(): int;

    /**
     * @return int
     */
    public function getTime(): int;

    /**
     * @return string
     */
    public function getTimeFormatted(): string;

    /**
     * @return float
     */
    public function getVolume(): float;

    /**
     * @return float
     */
    public function getPrice(): float;
}
