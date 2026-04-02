<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkerRestriction extends Model
{
    protected $fillable = ['worker_id', 'type', 'value', 'reason', 'valid_from', 'valid_until'];
    protected $casts = ['value' => 'array', 'valid_from' => 'date', 'valid_until' => 'date'];
    public function worker(): BelongsTo { return $this->belongsTo(Worker::class); }

    public function isActiveOn(string $date): bool
    {
        return $this->valid_from->lte($date) &&
               ($this->valid_until === null || $this->valid_until->gte($date));
    }
}
