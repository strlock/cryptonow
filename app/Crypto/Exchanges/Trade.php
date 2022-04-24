<?php
namespace App\Crypto\Exchanges;

use App\Crypto\Exchanges\TradeInterface;
use Stringable;

/**
 * Class Trade
 * @package App\Crypto\Exchanges
 */
class Trade implements TradeInterface, Stringable
{
    public const BUY = 1;
    public const SELL = -1;
    /**
     * Trade constructor.
     * @param int $id
     * @param int $time
     * @param float $price
     * @param float $volume
     */
    public function __construct(private int $id, private int $time, private float $price, private float $volume){}

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getTime(): int
    {
        return $this->time;
    }

    /**
     * @return string
     */
    public function getTimeFormatted(): string
    {
        return date('d.m.Y H:i:s', $this->time/1000);
    }

    /**
     * @return mixed
     */
    public function getVolume(): float
    {
        return $this->volume;
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'id' => $this->id,
            'time' => $this->time,
            'price' => $this->price,
            'volume' => $this->volume,
        ];
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return json_encode($this->toArray());
    }

    /**
     * @param string $json
     * @return static|null
     */
    public static function createFromJson(string $json): ?static {
        $result = null;
        $object = json_decode($json);
        if (!empty($object)) {
            $result = new static(
                id: $object->id,
                time: $object->time,
                price: $object->price,
                volume: $object->volume,
            );
        }
        return $result;
    }
}
