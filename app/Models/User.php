<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements UserInterface, JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'binance_api_key',
        'binance_api_secret',
        'ao_tp_percent',
        'ao_sl_percent',
        'ao_amount',
        'ao_limit_indent_percent',
        'ao_enabled',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * @return int
     */
    public function getJWTIdentifier()
    {
        return $this->getId();
    }

    /**
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $value
     * @return void
     */
    public function setName(string $value): void
    {
        $this->name = $value;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $value
     */
    public function setId(int $value): void
    {
        $this->id = $value;
    }

    /**
     * @return string
     */
    public function getBinanceApiKey(): string
    {
        return $this->binance_api_key;
    }

    /**
     * @param string $value
     */
    public function setBinanceApiKey(string $value): void
    {
        $this->binance_api_key = $value;
    }

    /**
     * @return string
     */
    public function getBinanceApiSecret(): string
    {
        return $this->binance_api_secret;
    }

    /**
     * @param string $value
     */
    public function setBinanceApiSecret(string $value): void
    {
        $this->binance_api_secret = $value;
    }

    /**
     * @return bool
     */
    public function isBinanceConnected(): bool
    {
        return !empty($this->binance_api_key) && !empty($this->binance_api_secret);
    }

    /**
     * @return float
     */
    public function getAOTpPercent(): float
    {
        return $this->ao_tp_percent;
    }

    /**
     * @return float
     */
    public function getAOSlPercent(): float
    {
        return $this->ao_sl_percent;
    }

    /**
     * @param float $value
     */
    public function setAOTpPercent(float $value): void
    {
        $this->ao_tp_percent = $value;
    }

    /**
     * @param float $value
     */
    public function setAOSlPercent(float $value): void
    {
        $this->ao_sl_percent = $value;
    }

    /**
     * @param float $value
     */
    public function setAOAmount(float $value): void
    {
        $this->ao_amount = $value;
    }

    /**
     * @return float
     */
    public function getAOAmount(): float
    {
        return $this->ao_amount;
    }

    /**
     * @param float $value
     */
    public function setAOLimitIndentPercent(float $value): void
    {
        $this->ao_limit_indent_percent = $value;
    }

    /**
     * @return float
     */
    public function getAOLimitIndentPercent(): float
    {
        return $this->ao_limit_indent_percent;
    }

    /**
     * @param bool $value
     */
    public function setAOEnabled(bool $value): void
    {
        $this->ao_enabled = $value;
    }

    /**
     * @return bool
     */
    public function isAOEnabled(): bool
    {
        return $this->ao_enabled;
    }
}
