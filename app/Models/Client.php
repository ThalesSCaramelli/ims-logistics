<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Client extends Model
{
    protected $fillable = [
        'name', 'abn', 'contact_name', 'contact_email',
        'contact_phone', 'requires_induction', 'notes', 'is_active'
    ];

    protected $casts = ['is_active' => 'boolean'];

    // ── Sites ─────────────────────────────────────────────────────────

    public function sites(): HasMany
    {
        return $this->hasMany(Site::class);
    }

    public function activeSites(): HasMany
    {
        return $this->hasMany(Site::class)->where('is_active', true);
    }

    // ── Container prices (client_container_prices) ────────────────────
    // Includes box/skill additional thresholds as columns on each row

    public function containerPrices(): HasMany
    {
        return $this->hasMany(ClientContainerPrice::class)
            ->orderBy('feet')
            ->orderBy('product_id');
    }

    // ── Hourly rates (client_hourly_rates) ────────────────────────────

    public function hourlyRates(): HasMany
    {
        return $this->hasMany(ClientHourlyRate::class);
    }

    // ── Container additionals (client_container_additionals) ──────────
    // Manual per-container flags: pallet removal, extra weight, etc.

    public function containerAdditionals(): HasMany
    {
        return $this->hasMany(ClientContainerAdditional::class)
            ->orderBy('sort_order');
    }

    // ── Helpers ───────────────────────────────────────────────────────

    /**
     * Get the container price row for a specific feet + product combination.
     * Falls back to product_id = null (Standard) if no exact match.
     */
    public function getContainerPrice(string $feet, ?int $productId): ?ClientContainerPrice
    {
        $prices = $this->containerPrices;

        // Exact match (feet + product)
        $match = $prices->first(fn($p) =>
            $p->feet === $feet && $p->product_id === $productId
        );
        if ($match) return $match;

        // Fallback to Standard (product_id = null) for this feet size
        return $prices->first(fn($p) =>
            $p->feet === $feet && is_null($p->product_id)
        );
    }

    /**
     * Get hourly rate for a service type.
     */
    public function getHourlyRate(string $serviceType): ?ClientHourlyRate
    {
        return $this->hourlyRates->where('service_type', $serviceType)->first();
    }
}