<?php


namespace App\Models;


interface UserInterface
{
    public function getId(): int;
    public function setId(int $value): void;
    public function getBinanceApiKey(): string;
    public function setBinanceApiKey(string $value): void;
    public function getBinanceApiSecret(): string;
    public function setBinanceApiSecret(string $value): void;
    public function isBinanceConnected(): bool;
}
