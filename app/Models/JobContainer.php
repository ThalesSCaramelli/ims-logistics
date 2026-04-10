<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobContainer extends Model
{
    protected $table = 'job_containers';

    protected $fillable = [
        'job_id', 'container_number', 'feet', 'product_id',
        'boxes_count', 'skills_count', 'description_extra',
    ];

    public function job(): BelongsTo { return $this->belongsTo(Job::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    
    public function workers(): HasMany {
        return $this->hasMany(ContainerWorker::class, 'container_id');
    }

    public function additionals(): HasMany
    {
        return $this->hasMany(JobContainerAdditional::class, 'container_id');
    }
}
