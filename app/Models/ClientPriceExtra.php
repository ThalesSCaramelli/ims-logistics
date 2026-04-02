<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClientPriceExtra extends Model
{
    protected $fillable = ['client_price_id', 'name', 'client_value', 'labor_value', 'unit', 'rule', 'condition'];
    protected $casts = ['client_value' => 'decimal:2', 'labor_value' => 'decimal:2'];

    public function clientPrice(): BelongsTo { return $this->belongsTo(ClientPrice::class); }
    public function isRequired(): bool { return $this->rule === 'required'; }
}
