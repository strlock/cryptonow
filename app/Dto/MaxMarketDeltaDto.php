<?php


namespace App\Dto;


class MaxMarketDeltaDto
{
    public function __construct(
        private float $marketDelta,
        private int $fromTime,
        private int $toTime,
        private float $fromPrice,
        private float $toPrice,
    )
    {
        //
    }

    /**
     * @return float
     */
    public function getMarketDelta(): float
    {
        return $this->marketDelta;
    }

    /**
     * @return int
     */
    public function getFromTime(): int
    {
        return $this->fromTime;
    }

    /**
     * @return int
     */
    public function getToTime(): int
    {
        return $this->toTime;
    }

    /**
     * @return float
     */
    public function getFromPrice(): float
    {
        return $this->fromPrice;
    }

    /**
     * @return float
     */
    public function getToPrice(): float
    {
        return $this->toPrice;
    }
}
