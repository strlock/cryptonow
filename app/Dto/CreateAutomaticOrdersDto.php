<?php


namespace App\Dto;


use App\Enums\OrderDirection;

class CreateAutomaticOrdersDto
{
    public function __construct(
        private string $symbol,
        private OrderDirection $direction,
    )
    {
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
     * @return OrderDirection
     */
    public function getDirection(): OrderDirection
    {
        return $this->direction;
    }

    /**
     * @return string
     */
    public function getPrice(): string
    {
        return $this->price;
    }

}
