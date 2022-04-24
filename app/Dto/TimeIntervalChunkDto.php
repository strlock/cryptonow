<?php

namespace App\Dto;

class TimeIntervalChunkDto
{
    public function __construct(private int $fromTime, private int $toTime) {
        //
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
