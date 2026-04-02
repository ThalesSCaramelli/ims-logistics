<?php

namespace App\Models;

use App\Enums\JobStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Job extends Model
{
    protected $fillable = [
        'book_id', 'site_id', 'date', 'start_time', 'status',
        'team_leader_id', 'started_at', 'completed_at', 'notes',
    ];

    protected $casts = [
        'status'       => JobStatus::class,
        'date'         => 'date',
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────────────────

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function teamLeader(): BelongsTo
    {
        // TL is any worker designated by the office — NOT a fixed system role
        // Permission check: $job->team_leader_id === auth()->user()->worker_id
        return $this->belongsTo(Worker::class, 'team_leader_id');
    }

    public function teams(): HasMany
    {
        return $this->hasMany(JobTeam::class);
    }

    public function containers(): HasMany
    {
        return $this->hasMany(JobContainer::class);
    }

    public function worksheet(): HasOne
    {
        return $this->hasOne(Worksheet::class);
    }

    // ── Helpers ────────────────────────────────────────────────────────

    public function isTeamLeader(int $workerId): bool
    {
        return $this->team_leader_id === $workerId;
    }

    public function workerParticipates(int $workerId): bool
    {
        return $this->book->workers()->where('worker_id', $workerId)->exists();
    }

    public function getStatusStringAttribute(): string
    {
        return $this->status instanceof \BackedEnum
            ? $this->status->value
            : (string) $this->status;
    }
}
