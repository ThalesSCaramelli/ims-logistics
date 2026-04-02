<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DayDemand extends Model
{
    protected $fillable = [
        'date', 'client_id', 'site_id', 'product_id',
        'qty_20ft', 'qty_40ft', 'qty_workers',
        'status', 'crews_allocated', 'notes', 'created_by',
    ];

    protected $casts = ['date' => 'date'];

    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
    public function site(): BelongsTo { return $this->belongsTo(Site::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function books(): HasMany { return $this->hasMany(Book::class, 'demand_id'); }

    public function totalContainers(): int { return $this->qty_20ft + $this->qty_40ft; }

    public function summaryLabel(): string
    {
        $parts = [];
        if ($this->qty_40ft > 0) $parts[] = $this->qty_40ft . '× 40ft';
        if ($this->qty_20ft > 0) $parts[] = $this->qty_20ft . '× 20ft';
        if ($this->qty_workers > 0) $parts[] = $this->qty_workers . ' worker' . ($this->qty_workers > 1 ? 's' : '');
        return implode(' + ', $parts) ?: 'No containers';
    }

    public function recalculateStatus(): void
    {
        if ($this->status === 'cancelled') return;
        $this->status = $this->crews_allocated === 0 ? 'pending' : 'partial';
        $this->save();
    }

    public function markAllocated(): void { $this->update(['status' => 'allocated']); }
    public function markCancelled(): void { $this->update(['status' => 'cancelled']); }

    public function incrementCrews(): void
    {
        $this->increment('crews_allocated');
        $this->recalculateStatus();
    }
}
