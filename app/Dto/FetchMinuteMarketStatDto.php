<?php

namespace App\Dto;

class FetchMinuteMarketStatDto
{
    public function __construct(
        private string $exchangeSymbol,
        private int $fromTime
    ) {
        //
    }

    /**
     * @return string
     */
    public function getExchangeSymbol(): string
    {
        return $this->exchangeSymbol;
    }

    /**
     * @return int
     */
    public function getFromTime(): int
    {
        return $this->fromTime;
    }
}
