<?php

namespace App\Jobs;

use App\Enums\TimeInterval;
use App\Services\Crypto\Exchanges\Factory as ExchangesFactory;
use App\Helpers\TimeHelper;
use App\Dto\FetchMinuteMarketStatDto;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class BitstampFetchMinuteMarketStat implements ShouldQueue, ShouldBeUnique
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
     */
    public function handle()
    {
        $symbol = $this->dto->getSymbol();
        $mdQueueName = 'bitstamp:md:'.$symbol;
        $fromTime = TimeHelper::round($this->dto->getFromTime(), TimeInterval::MINUTE());
        $fromDate = Date::createFromTimestampMs($fromTime);
        $nowDate = Date::now();
        $nowDate->setSeconds(0);
        $nowDate->setMicroseconds(0);
        $exchange = ExchangesFactory::create('bitstamp');
        $marketDelta = $exchange->getMinuteMarketDeltaFromDatabase($symbol, $fromTime);
        if ($marketDelta !== false) {
            Log::debug('BITSTAMP: Minute market stat already fetched.', ['symbol' => $symbol, 'fromTime' => date('d.m.Y H:i:s', $fromTime/1000)]);
            return;
        }
        if ($fromDate === $nowDate) {
            Log::debug('BITSTAMP: Minute market stat cannot be fetched for current minute.');
            return;
        }
        $toTime = $fromTime+TimeInterval::MINUTE()->value();
        $marketDelta = 0.0;
        foreach ($exchange->getTrades($symbol, $fromTime, $toTime) as $trade) {
            $marketDelta += $trade->getVolume();
        }
        Log::debug('BITSTAMP: Adding minute market stat to database. Market delta: '.$marketDelta, ['symbol' => $symbol, 'fromTime' => $fromTime]);
        Redis::zAdd($mdQueueName, $fromTime, $fromTime.':'.$marketDelta);
    }

    /**
     * @return string
     */
    public function uniqueId(): string
    {
        return $this->dto->getSymbol().':'.$this->dto->getFromTime();
    }
}
