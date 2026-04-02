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

    public function containerPrices(): HasMany {
        return $this->hasMany(ClientContainerPrice::class)->orderBy('feet')->orderBy('product_id');
    }
    public function boxAdditionals(): HasMany {
        return $this->hasMany(ClientBoxAdditional::class)->orderBy('feet');
    }
    public function skillAdditionals(): HasMany {
        return $this->hasMany(ClientSkillAdditional::class)->orderBy('feet');
    }
    
    // ── Sites ─────────────────────────────────────────────────────────
    public function sites(): HasMany
    {
        return $this->hasMany(Site::class);
    }

    public function activeSites(): HasMany
    {
        return $this->hasMany(Site::class)->where('is_active', true);
    }

    // ── Container prices ──────────────────────────────────────────────
    public function prices(): HasMany
    {
        return $this->hasMany(ClientPrice::class);
    }

    // ── Skills pricing ────────────────────────────────────────────────
    public function skillRate(): HasOne
    {
        return $this->hasOne(ClientSkillRate::class);
    }

    public function skillTiers(): HasMany
    {
        return $this->hasMany(WorkerSkillTier::class)->orderBy('sort_order');
    }

    // ── Boxes pricing ─────────────────────────────────────────────────
    public function boxRate(): HasOne
    {
        return $this->hasOne(ClientBoxRate::class);
    }

    public function boxTiers(): HasMany
    {
        return $this->hasMany(WorkerBoxTier::class)->orderBy('sort_order');
    }

    // ── Hourly services ───────────────────────────────────────────────
    public function hourlyRates(): HasMany {
        return $this->hasMany(ClientHourlyRate::class);
    }

    // ── Price lookup helpers ──────────────────────────────────────────

    /**
     * Get container price for a specific size and product.
     * Falls back: exact match → size only → product only → any.
     */
    public function getContainerPrice(?string $feet, ?int $productId): ?ClientPrice
    {
        $prices = $this->prices;

        // Try exact match first
        $match = $prices->first(fn($p) =>
            $p->feet == $feet && $p->product_id == $productId
        );
        if ($match) return $match;

        // Size only
        $match = $prices->first(fn($p) =>
            $p->feet == $feet && is_null($p->product_id)
        );
        if ($match) return $match;

        // Product only
        $match = $prices->first(fn($p) =>
            is_null($p->feet) && $p->product_id == $productId
        );
        if ($match) return $match;

        // Any (catch-all)
        return $prices->first(fn($p) => is_null($p->feet) && is_null($p->product_id));
    }

    /**
     * Get worker skill bonus for a given quantity using tiers.
     */
    public function getSkillBonus(int $qty): float
    {
        foreach ($this->skillTiers as $tier) {
            $inRange = $qty >= $tier->from_qty &&
                       (is_null($tier->to_qty) || $qty <= $tier->to_qty);
            if ($inRange) return (float) $tier->worker_bonus;
        }
        return 0.0;
    }

    /**
     * Get worker box bonus for a given quantity using tiers.
     */
    public function getBoxBonus(int $qty): float
    {
        foreach ($this->boxTiers as $tier) {
            $inRange = $qty >= $tier->from_qty &&
                       (is_null($tier->to_qty) || $qty <= $tier->to_qty);
            if ($inRange) return (float) $tier->worker_bonus;
        }
        return 0.0;
    }

    /**
     * Get hourly rate for a service type.
     */
    public function getHourlyRate(string $serviceType): ?ClientHourlyRate
    {
        return $this->hourlyRates->where('service_type', $serviceType)->first();
    }
}
