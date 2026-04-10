<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientContainerAdditional extends Model
{
    protected $fillable = [
        'client_id', 'name', 'feet', 'client_rate', 'worker_rate',
        'is_active', 'sort_order',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Whether this additional applies to the given container size.
     */
    public function appliesToFeet(string $feet): bool
    {
        return $this->feet === 'both' || $this->feet === $feet;
    }
}
