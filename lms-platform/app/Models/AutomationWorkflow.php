<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AutomationWorkflow extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'trigger_type',
        'conditions',
        'actions',
        'is_active',
        'last_run_at',
    ];

    protected function casts(): array
    {
        return [
            'conditions'  => 'array',
            'actions'     => 'array',
            'is_active'   => 'boolean',
            'last_run_at' => 'datetime',
        ];
    }

    // ── Relationships ─────────────────────────────────────────────

    public function logs(): HasMany
    {
        return $this->hasMany(AutomationLog::class, 'workflow_id');
    }

    // ── Scopes ─────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByTrigger($query, string $trigger)
    {
        return $query->where('trigger_type', $trigger);
    }
}
