<?php

namespace App\Models;

use App\Jobs\AyonSyncJob;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Talent extends Model
{
    use HasFactory;

    protected $table = 'talents';

    protected $fillable = [
        'username',
        'first_name',
        'last_name',
        'email',
        'synced_at',
    ];

    protected $casts = [
        'synced_at' => 'datetime',
    ];

    protected $appends = ['is_synced', 'sync_status'];

    protected static function booted()
    {
        static::created(fn ($talent) => AyonSyncJob::dispatch($talent, 'create'));
        static::updated(fn ($talent) => AyonSyncJob::dispatch($talent, 'update'));
        static::deleting(fn ($talent) => AyonSyncJob::dispatchSync($talent, 'delete'));
    }

    public function getIsSyncedAttribute(): bool
    {
        return $this->synced_at !== null &&
               $this->synced_at->greaterThanOrEqualTo($this->updated_at);
    }

    public function getSyncStatusAttribute(): string
    {
        if ($this->synced_at === null) {
            return 'never';
        }

        return $this->is_synced ? 'synced' : 'pending';
    }

    public function needsSync(): bool
    {
        return $this->synced_at === null ||
               $this->updated_at->isAfter($this->synced_at);
    }
}
