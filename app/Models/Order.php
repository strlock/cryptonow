<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
use DateTime;
use Illuminate\Support\Facades\Log;

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
        'ready_price',
        'completed_price',
        'symbol',
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
     * @return mixed
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param mixed $value
     */
    public function setType(string $value): void
    {
        $this->type = $value;
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
     * @return string
     */
    public function getState(): string
    {
        return $this->state;
    }

    /**
     * @param string $value
     */
    public function setState(string $value): void
    {
        $this->state = $value;
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
     * @return float
     */
    public function getReadyPrice(): float
    {
        return $this->ready_price;
    }

    /**
     * @param float $value
     */
    public function setReadyPrice(float $value): void
    {
        $this->ready_price = $value;
    }

    /**
     * @return float
     */
    public function getCompletedPrice(): float
    {
        return $this->completed_price;
    }

    /**
     * @param float $value
     */
    public function setCompletedPrice(float $value): void
    {
        $this->completed_price = $value;
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

    public function isSimple(): bool
    {
        return (float)$this->getSl() === 0.0 && (float)$this->getTp() === 0.0;
    }
}
