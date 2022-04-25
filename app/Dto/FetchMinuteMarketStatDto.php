<?php

namespace App\Dto;

class FetchMinuteMarketStatDto
{
    public function __construct(
        private string $symbol,
        private int $fromTime
    ) {
        //
    }

    /**
     * @return string
     */
    public function getSymbol(): string
    {
        return $this->symbol;
    }

    /**
     * @return int
     */
    public function getFromTime(): int
    {
        return $this->fromTime;
    }
}
