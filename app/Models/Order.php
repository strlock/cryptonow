<?php

namespace App\Models;

use App\Enums\OrderDirection;
use App\Enums\OrderState;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
use DateTime;

class Order extends Model implements OrderInterface
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'direction',
        'price',
        'amount',
        'sl',
        'tp',
        'market',
        'exchange',
        'state',
        'ready_at',
        'completed_at',
        'symbol',
        'exchange_order_id',
        'exchange_sl_order_id',
        'exchange_tp_order_id',
    ];

    /**
     * @return mixed
     */
    public function getUserId(): int
    {
        return $this->user_id;
    }

    /**
     * @param mixed $value
     */
    public function setUserId(int $value): void
    {
        $this->user_id = $value;
    }

    /**
     * @return OrderDirection
     */
    public function getDirection(): OrderDirection
    {
        return OrderDirection::memberByValue($this->direction);
    }

    /**
     * @param OrderDirection $value
     */
    public function setDirection(OrderDirection $value): void
    {
        $this->direction = $value->value();
    }

    /**
     * @return mixed
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * @param mixed $value
     */
    public function setPrice(float $value): void
    {
        $this->price = $value;
    }

    /**
     * @return mixed
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * @param mixed $value
     */
    public function setAmount(float $value): void
    {
        $this->amount = $value;
    }

    /**
     * @return mixed|null
     */
    public function getSl(): ?float
    {
        return $this->sl;
    }

    /**
     * @param mixed|null $value
     */
    public function setSl(?float $value): void
    {
        $this->sl = $value;
    }

    /**
     * @return mixed|null
     */
    public function getTp(): ?float
    {
        return $this->tp;
    }

    /**
     * @param mixed $value|null
     */
    public function setTp(?float $value): void
    {
        $this->tp = $value;
    }

    /**
     * @return mixed
     */
    public function isMarket(): bool
    {
        return $this->market;
    }

    /**
     * @param mixed $value
     */
    public function setMarket(bool $value): void
    {
        $this->market = $value;
    }

    /**
     * @return string
     */
    public function getExchange(): string
    {
        return $this->exchange;
    }

    /**
     * @param string $value
     */
    public function setExchange(string $value): void
    {
        $this->exchange = $value;
    }

    /**
     * @return OrderState
     */
    public function getState(): OrderState
    {
        return OrderState::memberByValue($this->state);
    }

    /**
     * @param OrderState $value
     */
    public function setState(OrderState $value): void
    {
        $this->state = $value->value();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $value
     */
    public function setId(int $value): void
    {
        $this->id = $value;
    }

    /**
     * @return DateTime|null
     */
    public function getReadyAt(): ?DateTime
    {
        return Date::parse($this->ready_at);
    }

    /**
     * @param DateTime|null $value
     */
    public function setReadyAt(?DateTime $value): void
    {
        $this->ready_at = $value->format('Y-m-d H:i:s');
    }

    /**
     * @return DateTime|null
     */
    public function getCompletedAt(): ?DateTime
    {
        return Date::parse($this->completed_at);
    }

    /**
     * @param DateTime|null $value
     */
    public function setCompletedAt(?DateTime $value): void
    {
        $this->completed_at = $value->format('Y-m-d H:i:s');
    }

    /**
     * @return string
     */
    public function getSymbol(): string
    {
        return $this->symbol;
    }

    /**
     * @param string $value
     */
    public function setSymbol(string $value): void
    {
        $this->symbol = $value;
    }

    public function hasGoal(): bool
    {
        return (float)$this->getSl() !== 0.0 || (float)$this->getTp() !== 0.0;
    }

    public function getExchangeOrderId(): string
    {
        return $this->exchange_order_id;
    }

    public function setExchangeOrderId(string $value): void
    {
        $this->exchange_order_id = $value;
    }

    public function getExchangeSlOrderId(): ?string
    {
        return $this->exchange_sl_order_id;
    }

    public function setExchangeSlOrderId(?string $value): void
    {
        $this->exchange_sl_order_id = $value;
    }

    public function getExchangeTpOrderId(): ?string
    {
        return $this->exchange_tp_order_id;
    }

    public function setExchangeTpOrderId(?string $value): void
    {
        $this->exchange_tp_order_id = $value;
    }
}
