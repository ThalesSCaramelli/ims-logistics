<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientContainerPrice extends Model
{
    protected $fillable = [
        'client_id', 'feet', 'product_id',
        'client_rate', 'worker_rate',
        'has_box_additional',
        'box_threshold', 'box_block_size',
        'box_client_rate_per_block', 'box_worker_rate_per_block',
        'has_skill_additional',
        'skill_threshold', 'skill_block_size',
        'skill_client_rate_per_block', 'skill_worker_rate_per_block',
        'is_active',
    ];

    protected $casts = [
        'has_box_additional'   => 'boolean',
        'has_skill_additional' => 'boolean',
        'is_active'            => 'boolean',
    ];

    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }

    /**
     * Calculate box additional charges for a given box count.
     * Returns ['client' => X, 'worker' => Y]
     */
    public function calcBoxAdditional(int $boxes): array
    {
        if (!$this->has_box_additional || !$this->box_threshold || !$this->box_block_size) {
            return ['client' => 0, 'worker' => 0];
        }
        $excess = max(0, $boxes - $this->box_threshold);
        $blocks = (int) ceil($excess / $this->box_block_size);
        return [
            'client' => $blocks * ($this->box_client_rate_per_block ?? 0),
            'worker' => $blocks * ($this->box_worker_rate_per_block ?? 0),
        ];
    }

    /**
     * Calculate skill additional charges for a given skill count.
     */
    public function calcSkillAdditional(int $skills): array
    {
        if (!$this->has_skill_additional || !$this->skill_threshold || !$this->skill_block_size) {
            return ['client' => 0, 'worker' => 0];
        }
        $excess = max(0, $skills - $this->skill_threshold);
        $blocks = (int) ceil($excess / $this->skill_block_size);
        return [
            'client' => $blocks * ($this->skill_client_rate_per_block ?? 0),
            'worker' => $blocks * ($this->skill_worker_rate_per_block ?? 0),
        ];
    }
}
