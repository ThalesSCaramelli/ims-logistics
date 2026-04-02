<?php

namespace App\Models;

use App\Enums\WorkerStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Worker extends Model
{
    protected $fillable = [
        'name', 'phone', 'email', 'abn', 'status', 'return_date', 'suspension_reason',
        'has_forklift', 'forklift_licence_number', 'forklift_expiry', 'forklift_state',
        'is_australian', 'min_weekly',
    ];

    protected $casts = [
        'status'          => WorkerStatus::class,
        'has_forklift'    => 'boolean',
        'is_australian'   => 'boolean',
        'return_date'     => 'date',
        'forklift_expiry' => 'date',
        'min_weekly'      => 'decimal:2',
    ];

    // ── Relationships ──────────────────────────────────────────────────

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function visa(): HasOne
    {
        return $this->hasOne(WorkerVisa::class);
    }

    public function inductions(): HasMany
    {
        return $this->hasMany(WorkerInduction::class);
    }

    public function restrictions(): HasMany
    {
        return $this->hasMany(WorkerRestriction::class);
    }

    public function books(): BelongsToMany
    {
        return $this->belongsToMany(Book::class, 'book_workers');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(WorkerPayment::class);
    }

    // ── Business logic helpers ─────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->status === WorkerStatus::Active;
    }

    public function isSuspended(): bool
    {
        return $this->status === WorkerStatus::Suspended;
    }

    public function hasBookOn(string $date): bool
    {
        return $this->books()->where('date', $date)->exists();
    }

    public function hasInductionFor(int $clientId): bool
    {
        return $this->inductions()
            ->where('client_id', $clientId)
            ->where('completed', true)
            ->exists();
    }

    public function hasActiveRestrictionForSite(int $siteId, string $date): bool
    {
        return $this->restrictions()
            ->where('type', 'site')
            ->whereJsonContains('value->site_id', $siteId)
            ->where('valid_from', '<=', $date)
            ->where(fn($q) => $q->whereNull('valid_until')->orWhere('valid_until', '>=', $date))
            ->exists();
    }

    public function hasActiveRestrictionForClient(int $clientId, string $date): bool
    {
        return $this->restrictions()
            ->where('type', 'client')
            ->whereJsonContains('value->client_id', $clientId)
            ->where('valid_from', '<=', $date)
            ->where(fn($q) => $q->whereNull('valid_until')->orWhere('valid_until', '>=', $date))
            ->exists();
    }

    public function visaIsExpiredOrExpiringSoon(int $days = 60): bool
    {
        if ($this->is_australian || !$this->visa) return false;
        return $this->visa->valid_until->lte(now()->addDays($days));
    }

    public function weeklyEarnings(string $weekStart): float
    {
        return (float) $this->payments()
            ->where('week_period', $weekStart)
            ->whereIn('status', ['calculated', 'approved', 'paid'])
            ->sum('total_amount');
    }
}
