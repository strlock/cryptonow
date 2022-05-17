<?php


namespace App\Services;


use App\Notifications\TelegramNotification;
use Illuminate\Support\Facades\Notification;

class TelegramService implements TelegramServiceInterface
{
    public function sendMessage(string $message): void
    {
        Notification::route('telegram', config('telegram.botChatId'))->notify(new TelegramNotification($message));
    }
}
