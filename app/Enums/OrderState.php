<?php

namespace App\Enums;

class OrderState
{
    public const NEW = 'new';
    public const READY = 'ready';
    public const PROFIT = 'profit';
    public const LOSS = 'loss';
    public const COMPLETED= 'completed';
    public const FAILED = 'failed';
    public const CANCELED = 'canceled';
}
