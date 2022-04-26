<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
use DateTime;

class Order extends Model implements OrderInterface
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'price',
        'amount',
        'sl',
        'tp',
        'market',
        'exchange',
        'state',
        'ready_at',
        'completed_at',
    ];

    /**
     * @return mixed
     */
    public function getUserId(): int
    {
        return $this->user_id;
    }

    /**
     * @param mixed $user_id
     */
    public function setUserId(int $user_id): void
    {
        $this->user_id = $user_id;
    }

    /**
     * @return mixed
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * @param mixed $price
     */
    public function setPrice(float $price): void
    {
        $this->price = $price;
    }

    /**
     * @return mixed
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * @param mixed $amount
     */
    public function setAmount(float $amount): void
    {
        $this->amount = $amount;
    }

    /**
     * @return mixed|null
     */
    public function getSl(): ?float
    {
        return $this->sl;
    }

    /**
     * @param mixed|null $sl
     */
    public function setSl(?float $sl): void
    {
        $this->sl = $sl;
    }

    /**
     * @return mixed|null
     */
    public function getTp(): ?float
    {
        return $this->tp;
    }

    /**
     * @param mixed $tp|null
     */
    public function setTp(?float $tp): void
    {
        $this->tp = $tp;
    }

    /**
     * @return mixed
     */
    public function getMarket(): bool
    {
        return $this->market;
    }

    /**
     * @param mixed $market
     */
    public function setMarket(bool $market): void
    {
        $this->market = $market;
    }

    public function getExchange(): string
    {
        return $this->exchange;
    }

    public function setExchange(string $exchange): void
    {
        $this->exchange = $exchange;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function setState(string $state): void
    {
        $this->state = $state;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getReadyAt(): ?DateTime
    {
        return Date::parse($this->ready_at);
    }

    public function setReadyAt(?DateTime $date): void
    {
        $this->ready_at = $date->format('Y-m-d H:i:s');
    }

    public function getCompletedAt(): ?DateTime
    {
        return Date::parse($this->completed_at);
    }

    public function setCompletedAt(?DateTime $date): void
    {
        $this->completed_at = $date->format('Y-m-d H:i:s');
    }
}
