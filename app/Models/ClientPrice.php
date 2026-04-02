<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClientPrice extends Model
{
    protected $fillable = [
        'client_id', 'site_id', 'product_id',
        'client_base_price', 'client_secondary_price',
        'client_boxes_limit', 'client_boxes_block_size', 'client_boxes_block_price',
        'client_skills_limit', 'client_skills_block_size', 'client_skills_block_price',
        'labor_base_price', 'labor_secondary_price',
        'labor_boxes_limit', 'labor_boxes_block_size', 'labor_boxes_block_price',
        'labor_skills_limit', 'labor_skills_block_size', 'labor_skills_block_price',
        'global_additionals',
        'extra_work_client_rate', 'extra_work_labor_rate',
        'waiting_time_client_rate', 'waiting_time_labor_rate',
        'is_active','worker_rate', 'client_rate',
    ];

    protected $casts = [
        'global_additionals'       => 'array',
        'client_base_price'        => 'decimal:2',
        'client_secondary_price'   => 'decimal:2',
        'client_boxes_block_price' => 'decimal:2',
        'client_skills_block_price'=> 'decimal:2',
        'labor_base_price'         => 'decimal:2',
        'labor_secondary_price'    => 'decimal:2',
        'labor_boxes_block_price'  => 'decimal:2',
        'labor_skills_block_price' => 'decimal:2',
        'extra_work_client_rate'   => 'decimal:2',
        'extra_work_labor_rate'    => 'decimal:2',
        'waiting_time_client_rate' => 'decimal:2',
        'waiting_time_labor_rate'  => 'decimal:2',
        'is_active'                => 'boolean',
    ];

    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
    public function site(): BelongsTo { return $this->belongsTo(Site::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function extras(): HasMany { return $this->hasMany(ClientPriceExtra::class); }

    public function requiredExtras(): HasMany { return $this->extras()->where('rule', 'required'); }
    public function optionalExtras(): HasMany { return $this->extras()->where('rule', 'optional'); }

    // Helper: find price config for a site + product
    public static function forSiteAndProduct(int $siteId, int $productId): ?self
    {
        $site = Site::findOrFail($siteId);
        return static::where('client_id', $site->client_id)
            ->where('site_id', $siteId)
            ->where('product_id', $productId)
            ->where('is_active', true)
            ->first();
    }

    // Calculate boxes surcharge (client side)
    public function clientBoxesSurcharge(int $boxesCount): float
    {
        if (!$this->client_boxes_limit || $boxesCount <= $this->client_boxes_limit) return 0;
        $exceeded = $boxesCount - $this->client_boxes_limit;
        $blocks = ceil($exceeded / $this->client_boxes_block_size);
        return $blocks * (float) $this->client_boxes_block_price;
    }

    // Calculate boxes surcharge (labor side)
    public function laborBoxesSurcharge(int $boxesCount): float
    {
        if (!$this->labor_boxes_limit || $boxesCount <= $this->labor_boxes_limit) return 0;
        $exceeded = $boxesCount - $this->labor_boxes_limit;
        $blocks = ceil($exceeded / $this->labor_boxes_block_size);
        return $blocks * (float) $this->labor_boxes_block_price;
    }

    // Calculate skills surcharge (client side)
    public function clientSkillsSurcharge(int $skillsCount): float
    {
        if (!$this->client_skills_limit || $skillsCount <= $this->client_skills_limit) return 0;
        $exceeded = $skillsCount - $this->client_skills_limit;
        $blocks = ceil($exceeded / $this->client_skills_block_size);
        return $blocks * (float) $this->client_skills_block_price;
    }

    // Calculate skills surcharge (labor side)
    public function laborSkillsSurcharge(int $skillsCount): float
    {
        if (!$this->labor_skills_limit || $skillsCount <= $this->labor_skills_limit) return 0;
        $exceeded = $skillsCount - $this->labor_skills_limit;
        $blocks = ceil($exceeded / $this->labor_skills_block_size);
        return $blocks * (float) $this->labor_skills_block_price;
    }
}
