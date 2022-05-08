<?php


namespace App\Models;


interface UserInterface
{
    /**
     * @return int
     */
    public function getId(): int;

    /**
     * @param int $value
     */
    public function setId(int $value): void;

    /**
     * @return string
     */
    public function getBinanceApiKey(): string;

    /**
     * @param string $value
     */
    public function setBinanceApiKey(string $value): void;

    /**
     * @return string
     */
    public function getBinanceApiSecret(): string;

    /**
     * @param string $value
     */
    public function setBinanceApiSecret(string $value): void;

    /**
     * @return bool
     */
    public function isBinanceConnected(): bool;

    /**
     * @return float
     */
    public function getAOTpPercent(): float;

    /**
     * @return float
     */
    public function getAOSlPercent(): float;

    /**
     * @param float $value
     */
    public function setAOTpPercent(float $value): void;

    /**
     * @param float $value
     */
    public function setAOSlPercent(float $value): void;

    /**
     * @param float $value
     */
    public function setAOAmount(float $value): void;

    /**
     * @return float
     */
    public function getAOAmount(): float;

    /**
     * @param float $value
     */
    public function setAOLimitIndentPercent(float $value): void;

    /**
     * @return float
     */
    public function getAOLimitIndentPercent(): float;

    /**
     * @param bool $value
     */
    public function setAOEnabled(bool $value): void;

    /**
     * @return bool
     */
    public function isAOEnabled(): bool;
}
