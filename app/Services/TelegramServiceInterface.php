<?php


namespace App\Services;


interface TelegramServiceInterface
{
    public function sendMessage(string $message): void;
}
