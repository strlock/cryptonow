<?php


namespace App\Models;


use DateTime;

interface OrderInterface
{
    public function getId(): int;
    public function setId(int $value): void;
    public function getUserId(): int;
    public function setUserId(int $value): void;
    public function getType(): string;
    public function setType(string $value): void;
    public function getPrice(): float;
    public function setPrice(float $value): void;
    public function getAmount(): float;
    public function setAmount(float $value): void;
    public function getSl(): ?float;
    public function setSl(?float $value): void;
    public function getTp(): ?float;
    public function setTp(?float $value): void;
    public function getMarket(): bool;
    public function setMarket(bool $value): void;
    public function getExchange(): string;
    public function setExchange(string $value): void;
    public function getState(): string;
    public function setState(string $value): void;
    public function getReadyAt(): ?DateTime;
    public function setReadyAt(?DateTime $value): void;
    public function getCompletedAt(): ?DateTime;
    public function setCompletedAt(?DateTime $value): void;
    public function getReadyPrice(): float;
    public function setReadyPrice(float $value): void;
    public function getCompletedPrice(): float;
    public function setCompletedPrice(float $value): void;
    public function getSymbol(): string;
    public function setSymbol(string $value): void;
}
