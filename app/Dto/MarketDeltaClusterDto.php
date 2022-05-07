<?php


namespace App\Dto;


class MarketDeltaClusterDto
{
    public function __construct(
        private float $marketDelta,
        private int $fromTime,
        private int $toTime,
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


}
