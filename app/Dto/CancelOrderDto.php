<?php


namespace App\Dto;


use App\Enums\ExchangeOrderType;
use App\Enums\OrderDirection;

class CancelOrderDto
{
    public function __construct(
        private string $exchangeSymbol,
        private string $orderId,
    )
    {
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
     * @return string
     */
    public function getOrderId(): string
    {
        return $this->orderId;
    }
}
