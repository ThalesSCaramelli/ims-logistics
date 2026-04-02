<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorksheetService extends Model
{
    protected $fillable = ['worksheet_id', 'service_type', 'hours', 'description'];

    public function worksheet(): BelongsTo { return $this->belongsTo(Worksheet::class); }
}
