<?php

namespace App\Enums;

class BinanceOrderExecutionType
{
    public const NEW = 'NEW';
    public const CANCELED = 'CANCELED';
    public const REPLACED = 'REPLACED';
    public const REJECTED = 'REJECTED';
    public const TRADE = 'TRADE';
    public const EXPIRED = 'EXPIRED';
}
