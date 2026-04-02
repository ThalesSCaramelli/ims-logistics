<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class ClientBoxAdditional extends Model {
    protected $fillable = ['client_id','threshold','block_size','client_rate_per_block','worker_rate_per_block','is_active'];
    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
}