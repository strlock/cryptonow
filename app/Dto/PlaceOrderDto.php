<?php


namespace App\Dto;


use App\Enums\ExchangeOrderType;
use App\Enums\OrderDirection;

class PlaceOrderDto
{
    public function __construct(
        private OrderDirection $direction,
        private string $symbol,
        private float $amount,
        private float $price,
        private ExchangeOrderType $orderType,
        private string $clientOrderId,
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
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * @return ExchangeOrderType
     */
    public function getOrderType(): ExchangeOrderType
    {
        return $this->orderType;
    }

    /**
     * @return string
     */
    public function getClientOrderId(): string
    {
        return $this->clientOrderId;
    }
}
