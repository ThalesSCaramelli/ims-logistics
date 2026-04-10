<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobContainerAdditional extends Model
{
    protected $fillable = ['container_id', 'additional_id'];

    public function container(): BelongsTo
    {
        return $this->belongsTo(JobContainer::class, 'container_id');
    }

    public function additional(): BelongsTo
    {
        return $this->belongsTo(ClientContainerAdditional::class, 'additional_id');
    }
}
