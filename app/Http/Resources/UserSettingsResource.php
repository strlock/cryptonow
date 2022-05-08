<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class UserSettingsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        /** @var User $user */
        $user = $this->resource;
        return [
            'binance_api_key' => $user->getBinanceApiKey(),
            'binance_api_secret' => $user->getBinanceApiSecret(),
            'ao_tp_percent' => $user->getAOTpPercent(),
            'ao_sl_percent' => $user->getAOSlPercent(),
            'ao_amount' => $user->getAOAmount(),
            'ao_limit_indent_percent' => $user->getAOLimitIndentPercent(),
            'ao_enabled' => $user->isAOEnabled(),
        ];
    }
}
