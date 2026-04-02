<?php
// ============================================================
// CREATE THESE 4 MODEL FILES:
// ============================================================

// ── app/Models/ClientSkillRate.php ────────────────────────────────────
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class ClientSkillRate extends Model {
    protected $fillable = ['client_id','client_rate_per_skill'];
    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
}

// ── app/Models/ClientBoxRate.php ──────────────────────────────────────
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class ClientBoxRate extends Model {
    protected $fillable = ['client_id','client_rate_per_box'];
    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
}

// ── app/Models/ClientHourlyRate.php ───────────────────────────────────
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class ClientHourlyRate extends Model {
    protected $fillable = ['client_id','service_type','label','client_rate_per_hour','worker_rate_per_hour','is_active'];
    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
}

// ── app/Models/WorkerSkillTier.php ────────────────────────────────────
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class WorkerSkillTier extends Model {
    protected $fillable = ['client_id','from_qty','to_qty','worker_bonus','sort_order'];
    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
}

// ── app/Models/WorkerBoxTier.php ──────────────────────────────────────
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class WorkerBoxTier extends Model {
    protected $fillable = ['client_id','from_qty','to_qty','worker_bonus','sort_order'];
    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
}
