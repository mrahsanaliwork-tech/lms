<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssignmentSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'assignment_id',
        'student_id',
        'file_path',
        'notes',
        'submitted_at',
        'grade',
        'feedback',
        'graded_at',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'graded_at'    => 'datetime',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    // ── Scopes ─────────────────────────────────────────────────────

    public function scopeGraded($query)
    {
        return $query->whereNotNull('graded_at');
    }

    public function scopePending($query)
    {
        return $query->whereNull('graded_at');
    }
}
