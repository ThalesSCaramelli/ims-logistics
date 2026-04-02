<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class ClientHourlyRate extends Model {
    protected $fillable = ['client_id','service_type','client_rate_per_hour','worker_rate_per_hour','holiday_multiplier','is_active'];
    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
}