<?php

namespace App\Jobs;

use App\Enums\TimeInterval;
use App\Models\MarketDelta;
use App\Services\Crypto\Exchanges\Factory as ExchangesFactory;
use App\Services\Crypto\Exchanges\TradeInterface;
use App\Helpers\TimeHelper;
use App\Dto\FetchMinuteMarketStatDto;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class BitfinexFetchMinuteMarketStat implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(private FetchMinuteMarketStatDto $dto)
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws Exception
     */
    public function handle()
    {
        $exchange = ExchangesFactory::create('bitfinex');
        $exchangeSymbol = $this->dto->getExchangeSymbol();
        $mdQueueName = 'bitfinex:md:'.$exchangeSymbol;
        $fromTime = TimeHelper::round($this->dto->getFromTime(), TimeInterval::MINUTE());
        $fromDate = Date::createFromTimestampMs($fromTime);
        $nowDate = Date::now();
        $nowDate->setSeconds(0);
        $nowDate->setMicroseconds(0);
        $marketDelta = $exchange->getMinuteMarketDeltaFromDatabase($exchangeSymbol, $fromTime);
        if ($marketDelta !== false) {
            Log::debug('BITFINEX: Minute market stat already fetched.', ['symbol' => $exchangeSymbol, 'fromTime' => date('d.m.Y H:i:s', $fromTime/1000)]);
            return;
        }
        if ($fromDate === $nowDate) {
            Log::debug('BITFINEX: Minute market stat cannot be fetched for current minute.');
            return;
        }
        $toTime = $fromTime+TimeInterval::MINUTE()->value();
        $marketDelta = 0.0;
        foreach ($exchange->getTrades($exchangeSymbol, $fromTime, $toTime) as $trade) {
            /** @var TradeInterface $trade */
            $marketDelta += $trade->getVolume();
        }
        Log::debug('BITFINEX: Adding minute market stat to database. Market delta: '.$marketDelta, ['symbol' => $exchangeSymbol, 'fromTime' => $fromTime]);
        MarketDelta::updateOrCreate([
            'symbol' => $exchangeSymbol,
            'exchange' => 'bitfinex',
            'time' => $fromTime,
        ], [
            'value' => $marketDelta,
        ]);
    }

    /**
     * @return string
     */
    public function uniqueId(): string
    {
        return $this->dto->getExchangeSymbol().':'.$this->dto->getFromTime();
    }
}
