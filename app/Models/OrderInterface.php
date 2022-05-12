<?php


namespace App\Models;


use App\Enums\OrderDirection;
use App\Enums\OrderState;
use DateTime;

interface OrderInterface
{
    public function getId(): int;
    public function setId(int $value): void;
    public function getUserId(): int;
    public function setUserId(int $value): void;
    public function getDirection(): OrderDirection;
    public function setDirection(OrderDirection $value): void;
    public function getPrice(): float;
    public function setPrice(float $value): void;
    public function getAmount(): float;
    public function setAmount(float $value): void;
    public function getSl(): ?float;
    public function setSl(?float $value): void;
    public function getTp(): ?float;
    public function setTp(?float $value): void;
    public function isMarket(): bool;
    public function setMarket(bool $value): void;
    public function getExchange(): string;
    public function setExchange(string $value): void;
    public function getState(): OrderState;
    public function setState(OrderState $value): void;
    public function getReadyAt(): ?DateTime;
    public function setReadyAt(?DateTime $value): void;
    public function getCompletedAt(): ?DateTime;
    public function setCompletedAt(?DateTime $value): void;
    public function getSymbol(): string;
    public function setSymbol(string $value): void;
    public function hasGoal(): bool;
    public function getExchangeOrderId(): ?string;
    public function setExchangeOrderId(?string $value): void;
    public function getExchangeSlOrderId(): ?string;
    public function setExchangeSlOrderId(?string $value): void;
    public function getExchangeTpOrderId(): ?string;
    public function setExchangeTpOrderId(?string $value): void;
    public function getCreatedPrice(): ?float;
    public function setCreatedPrice(?float $value): void;
    public function getReadyPrice(): ?float;
    public function setReadyPrice(?float $value): void;
    public function getClosedPrice(): ?float;
    public function setClosedPrice(?float $value): void;
    public function getDiffPercent(): ?float;
    public function setDiffPercent(?float $value): void;
}
