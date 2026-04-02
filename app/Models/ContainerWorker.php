<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContainerWorker extends Model
{
    protected $fillable = ['container_id', 'worker_id', 'part', 'qty'];

    public function container(): BelongsTo { return $this->belongsTo(JobContainer::class, 'container_id'); }
    public function worker(): BelongsTo { return $this->belongsTo(Worker::class); }
}
