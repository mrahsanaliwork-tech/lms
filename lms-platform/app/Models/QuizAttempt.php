<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'quiz_id',
        'answers',
        'question_order',
        'score',
        'total_marks',
        'passed',
        'needs_manual_grading',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'answers'              => 'array',
            'question_order'       => 'array',
            'passed'               => 'boolean',
            'needs_manual_grading' => 'boolean',
            'started_at'           => 'datetime',
            'completed_at'         => 'datetime',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    // ── Scopes ─────────────────────────────────────────────────────

    public function scopePassed($query)
    {
        return $query->where('passed', true);
    }

    public function scopeCompleted($query)
    {
        return $query->whereNotNull('completed_at');
    }
}
