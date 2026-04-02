<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientPriceHistory extends Model
{
    protected $table = 'client_price_history'; // ← adicionar esta linha
    public $timestamps = false;

    protected $fillable = [
        'client_id', 'section', 'previous_data', 'new_data', 'changed_by', 'changed_at',
    ];

    protected $casts = [
        'previous_data' => 'array',
        'new_data'      => 'array',
        'changed_at'    => 'datetime',
    ];

    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
    public function changedBy(): BelongsTo { return $this->belongsTo(User::class, 'changed_by'); }
}
