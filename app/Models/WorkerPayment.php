<?php
namespace App\Models;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkerPayment extends Model
{
    protected $fillable = [
        'worker_id', 'worksheet_id', 'job_container_team_id',
        'base_amount', 'extras_amount', 'topup_amount', 'total_amount',
        'description', 'week_period', 'status', 'paid_at', 'payment_reference', 'approved_by',
    ];
    protected $casts = [
        'status'       => PaymentStatus::class,
        'base_amount'  => 'decimal:2',
        'extras_amount'=> 'decimal:2',
        'topup_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'week_period'  => 'date',
        'paid_at'      => 'datetime',
    ];

    public function worker(): BelongsTo { return $this->belongsTo(Worker::class); }
    public function worksheet(): BelongsTo { return $this->belongsTo(Worksheet::class); }
    public function containerTeam(): BelongsTo { return $this->belongsTo(JobContainerTeam::class, 'job_container_team_id'); }
    public function approvedBy(): BelongsTo { return $this->belongsTo(User::class, 'approved_by'); }
}
