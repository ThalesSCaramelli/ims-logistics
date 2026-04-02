<?php
namespace App\Models;
use App\Enums\ProductType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = ['name', 'type', 'description', 'is_default', 'has_boxes_skills', 'is_active'];
    protected $casts = [
        'type'             => ProductType::class,
        'is_default'       => 'boolean',
        'has_boxes_skills' => 'boolean',
        'is_active'        => 'boolean',
    ];

    public function prices(): HasMany { return $this->hasMany(ClientPrice::class); }

    public function isHourly(): bool { return $this->type === ProductType::Hour; }
    public function isContainer(): bool { return $this->type === ProductType::Container; }
    public function isMixed(): bool { return $this->type === ProductType::Mixed; }

    public static function default(): self
    {
        return static::where('is_default', true)->firstOrFail();
    }
}
