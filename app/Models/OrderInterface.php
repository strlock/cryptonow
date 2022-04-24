<?php


namespace App\Models;


interface OrderInterface
{
    public function getId(): int;
    public function setId(int $id): void;
    public function getUserId(): int;
    public function setUserId(int $user_id): void;
    public function getType(): string;
    public function setType(string $type): void;
    public function getPrice(): float;
    public function setPrice(float $price): void;
    public function getAmount(): float;
    public function setAmount(float $amount): void;
    public function getSl(): ?float;
    public function setSl(?float $sl): void;
    public function getTp(): ?float;
    public function setTp(?float $tp): void;
    public function getMarket(): bool;
    public function setMarket(bool $market): void;
    public function getExchange(): string;
    public function setExchange(string $exchange): void;
    public function getState(): string;
    public function setState(string $state): void;
}
