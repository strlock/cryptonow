<?php


namespace App\Dto;


use App\Enums\OrderDirection;

class PlaceGoalOrderDto
{
    public function __construct(
        private OrderDirection $direction,
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
     * @return OrderDirection
     */
    public function getDirection(): OrderDirection
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
