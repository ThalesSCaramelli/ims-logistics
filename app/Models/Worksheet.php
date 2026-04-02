<?php
namespace App\Models;
use App\Enums\WorksheetStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Worksheet extends Model
{
    protected $fillable = [
        'job_id', 'filled_by', 'filled_data', 'filled_at',
        'submitted_by', 'submitted_at',
        'client_signature_type', 'client_signature_data', 'client_signed_by',
        'waived_by', 'waived_reason', 'waived_at',
        'extra_work', 'waiting_time', 'attachments',
        'approved_by', 'approved_at', 'corrections',
        'sync_status', 'synced_at', 'observations',
    ];

    protected $casts = [
        'sync_status'  => WorksheetStatus::class,
        'filled_data'  => 'array',
        'extra_work'   => 'array',
        'waiting_time' => 'array',
        'attachments'  => 'array',
        'corrections'  => 'array',
        'filled_at'    => 'datetime',
        'submitted_at' => 'datetime',
        'approved_at'  => 'datetime',
        'waived_at'    => 'datetime',
        'synced_at'    => 'datetime',
    ];

    public function job(): BelongsTo { return $this->belongsTo(Job::class); }
    public function filledBy(): BelongsTo { return $this->belongsTo(Worker::class, 'filled_by'); }
    public function submittedBy(): BelongsTo { return $this->belongsTo(Worker::class, 'submitted_by'); }
    public function approvedBy(): BelongsTo { return $this->belongsTo(User::class, 'approved_by'); }
    public function waivedBy(): BelongsTo { return $this->belongsTo(User::class, 'waived_by'); }
    public function payments(): HasMany { return $this->hasMany(WorkerPayment::class); }

    public function isPending(): bool { return $this->sync_status === WorksheetStatus::Pending; }
    public function isApproved(): bool { return $this->sync_status === WorksheetStatus::Approved; }
    public function isPaid(): bool { return $this->sync_status === WorksheetStatus::Paid; }

    public function totalExtraWorkHours(): float
    {
        return collect($this->extra_work ?? [])->sum('hours');
    }

    public function totalWaitingTimeHours(): float
    {
        return collect($this->waiting_time ?? [])->sum('hours');
    }

    // Add a correction entry to history
    public function addCorrection(string $field, mixed $oldValue, mixed $newValue, string $reason, int $userId): void
    {
        $corrections = $this->corrections ?? [];
        $corrections[] = [
            'field'     => $field,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'reason'    => $reason,
            'by'        => $userId,
            'at'        => now()->toISOString(),
        ];
        $this->update(['corrections' => $corrections]);
    }
    
    public function labourLines(): HasMany
    {
        return $this->hasMany(WorksheetLabourLine::class);
    }
 
    public function extras(): HasMany
    {
        return $this->hasMany(WorksheetExtra::class);
    }

    public function services(): HasMany {
        return $this->hasMany(WorksheetService::class);
    }

}
