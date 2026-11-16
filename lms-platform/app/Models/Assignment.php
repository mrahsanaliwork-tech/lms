<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'instructor_id',
        'title',
        'description',
        'due_date',
        'max_marks',
        'allow_late_submission',
    ];

    protected function casts(): array
    {
        return [
            'due_date'             => 'datetime',
            'allow_late_submission' => 'boolean',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

    // ── Scopes ─────────────────────────────────────────────────────

    public function scopeUpcoming($query)
    {
        return $query->where('due_date', '>', now());
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now());
    }
}
