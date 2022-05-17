<?php


namespace App\Exceptions;


use App\Models\OrderInterface;
use Exception;

class CannotPlaceExchangeOrderException extends Exception
{
    public function __construct(private OrderInterface $order)
    {
        $message = 'Cannot place order '.$order->getId().' to exchange';
        parent::__construct($message,0, null);
    }

    /**
     * @return OrderInterface
     */
    public function getOrder(): OrderInterface
    {
        return $this->order;
    }
}
