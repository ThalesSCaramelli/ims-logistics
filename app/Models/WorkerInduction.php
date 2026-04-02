<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkerInduction extends Model
{
    protected $fillable = ['worker_id', 'client_id', 'completed'];
    protected $casts = ['completed' => 'boolean'];
    public function worker(): BelongsTo { return $this->belongsTo(Worker::class); }
    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
}
