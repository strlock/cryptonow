<?php
namespace App\Crypto\Exchanges;

use App\Crypto\Helpers\TimeHelper;
use Illuminate\Support\Collection;

/**
 * Interface FacadeInterface
 * @package App\Crypto\Exchanges
 */
interface FacadeInterface
{
    /**
     * @param string $symbol
     * @param int $fromTime
     * @param int|null $toTime
     * @return array
     */
    public function getTrades(string $symbol, int $fromTime, int $toTime = null): Collection;

    /**
     * @param string $symbol
     * @param int $fromTime
     * @param int|null $toTime
     * @return Collection
     */
    public function getCandlesticks(string $symbol, int $fromTime, int $toTime = null, int $interval = TimeHelper::FIVE_MINUTE_MS): Collection;
}
