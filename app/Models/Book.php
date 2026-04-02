<?php

namespace App\Models;

use App\Enums\BookStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    protected $fillable = [
        'date', 'status', 'notes', 'created_by',
        'notified_at', 'notified_by', 'demand_id',
    ];

    protected $casts = [
        'status'      => BookStatus::class,
        'date'        => 'date',
        'notified_at' => 'datetime',
    ];

    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function notifiedBy(): BelongsTo { return $this->belongsTo(User::class, 'notified_by'); }
    public function demand(): BelongsTo { return $this->belongsTo(DayDemand::class, 'demand_id'); }
    public function workers(): BelongsToMany { return $this->belongsToMany(Worker::class, 'book_workers'); }
    public function jobs(): HasMany { return $this->hasMany(Job::class); }
}
