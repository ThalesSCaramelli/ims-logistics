<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkerVisa extends Model
{
    protected $fillable = ['worker_id', 'visa_class', 'valid_until', 'work_permitted', 'fortnightly_hours_limit', 'document_path'];
    protected $casts = ['valid_until' => 'date', 'work_permitted' => 'boolean'];

    public function worker(): BelongsTo { return $this->belongsTo(Worker::class); }

    public function isExpired(): bool { return $this->valid_until->isPast(); }
    public function isExpiringSoon(int $days = 60): bool { return $this->valid_until->lte(now()->addDays($days)); }
    public function isExpiredOrExpiringSoon(int $days = 60): bool { return $this->isExpired() || $this->isExpiringSoon($days); }
}
