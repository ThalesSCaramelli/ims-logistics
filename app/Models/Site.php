<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Site extends Model
{
    protected $fillable = ['client_id', 'name', 'address', 'contact_person', 'phone', 'requires_photos', 'skill_label', 'is_active'];
    protected $casts = ['requires_photos' => 'boolean', 'is_active' => 'boolean'];

    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
    public function jobs(): HasMany { return $this->hasMany(Job::class); }
    public function prices(): HasMany { return $this->hasMany(ClientPrice::class); }
}
