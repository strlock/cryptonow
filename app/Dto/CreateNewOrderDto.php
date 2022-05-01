<?php


namespace App\Dto;


use App\Enums\OrderState;
use Illuminate\Contracts\Support\Arrayable;

class CreateNewOrderDto implements Arrayable
{
    public function __construct(
        private int $userId,
        private string $direction,
        private float $price,
        private float $amount,
        private ?float $sl,
        private ?float $tp,
        private bool $market,
        private string $exchange,
        private string $symbol,
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
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @return int
     */
    public function getDirection(): int
    {
        return $this->direction;
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
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
     * @return bool
     */
    public function isMarket(): bool
    {
        return $this->market;
    }

    /**
     * @return string
     */
    public function getExchange(): string
    {
        return $this->exchange;
    }

    public function toArray()
    {
        return [
            'user_id' => $this->userId,
            'direction' => $this->direction,
            'price' => $this->price,
            'amount' => $this->amount,
            'sl' => $this->sl,
            'tp' => $this->tp,
            'market' => $this->market,
            'exchange' => $this->exchange,
            'symbol' => $this->symbol,
        ];
    }
}
