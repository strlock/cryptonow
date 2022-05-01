<?php


namespace App\Dto;


class PlaceGoalOrderDto
{
    public function __construct(
        private string $direction,
        private string $symbol,
        private float $amount,
        private ?float $sl,
        private ?float $tp,
        private ?int $orderId,
    )
    {
        //
    }

    /**
     * @return string
     */
    public function getDirection(): string
    {
        return $this->direction;
    }

    /**
     * @return string
     */
    public function getSymbol(): string
    {
        return $this->symbol;
    }

    /**
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * @return float|null
     */
    public function getSl(): ?float
    {
        return $this->sl;
    }

    /**
     * @return float|null
     */
    public function getTp(): ?float
    {
        return $this->tp;
    }

    /**
     * @return int|null
     */
    public function getOrderId(): ?int
    {
        return $this->orderId;
    }
}
