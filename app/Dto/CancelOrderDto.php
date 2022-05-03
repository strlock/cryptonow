<?php


namespace App\Dto;


use App\Enums\ExchangeOrderType;
use App\Enums\OrderDirection;

class CancelOrderDto
{
    public function __construct(
        private string $symbol,
        private string $orderId,
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
     * @return string
     */
    public function getOrderId(): string
    {
        return $this->orderId;
    }
}
