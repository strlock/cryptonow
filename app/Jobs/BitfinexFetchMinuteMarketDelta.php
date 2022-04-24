<?php

namespace App\Jobs;

use App\Crypto\Exchanges\AbstractFacade as ExchangeFacade;
use App\Crypto\Exchanges\Factory as ExchangesFactory;
use App\Crypto\Exchanges\TradeInterface;
use App\Crypto\Helpers\TimeHelper;
use App\Dto\FetchMinuteMarketDeltaDto;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class BitfinexFetchMinuteMarketDelta implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(private FetchMinuteMarketDeltaDto $dto)
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
        $mdQueueName = 'bitfinex:md:'.$symbol;
        $fromTime = TimeHelper::roundTimestampMs($this->dto->getFromTime());
        $fromDate = Date::createFromTimestampMs($fromTime);
        $nowDate = Date::now();
        $nowDate->setSeconds(0);
        $nowDate->setMicroseconds(0);
        /** @var ExchangeFacade $exchange */
        $exchange = ExchangesFactory::create('bitfinex');
        $marketDelta = $exchange->getMinuteMarketDeltaFromDatabase($symbol, $fromTime);
        if ($marketDelta !== false) {
            Log::debug('BITFINEX: Minute market delta already fetched.', ['symbol' => $symbol, 'fromTime' => date('d.m.Y H:i:s', $fromTime/1000)]);
            return;
        }
        if ($fromDate === $nowDate) {
            Log::debug('BITFINEX: Minute market delta cannot be fetched for current minute.');
            return;
        }
        $toTime = $fromTime+TimeHelper::MINUTE_MS;
        $marketDelta = 0.0;
        foreach ($exchange->getTrades($symbol, $fromTime, $toTime) as $trade) {
            /** @var TradeInterface $trade */
            $marketDelta += $trade->getVolume();
        }
        Log::debug('BITFINEX: Adding minute market delta to database. Market delta: '.$marketDelta, ['symbol' => $symbol, 'fromTime' => $fromTime]);
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
