<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobContainerTeam extends Model
{
    protected $fillable = ['job_container_id', 'job_team_id', 'feet_completed', 'notes'];

    public function container(): BelongsTo { return $this->belongsTo(JobContainer::class, 'job_container_id'); }
    public function team(): BelongsTo { return $this->belongsTo(JobTeam::class, 'job_team_id'); }
    public function payments(): HasMany { return $this->hasMany(WorkerPayment::class); }

    public function percentageOfContainer(): float
    {
        $totalFeet = (int) $this->container->feet;
        return round(($this->feet_completed / $totalFeet) * 100, 2);
    }
}
