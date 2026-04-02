<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobTeam extends Model
{
    protected $fillable = ['job_id', 'name', 'created_by'];

    public function job(): BelongsTo { return $this->belongsTo(Job::class); }
    public function creator(): BelongsTo { return $this->belongsTo(Worker::class, 'created_by'); }
    public function workers(): BelongsToMany { return $this->belongsToMany(Worker::class, 'job_team_workers'); }
    public function containerTeams(): HasMany { return $this->hasMany(JobContainerTeam::class, 'job_team_id'); }
}
