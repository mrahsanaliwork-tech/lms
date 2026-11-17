<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'instructor_id',
        'category_id',
        'thumbnail',
        'price',
        'is_free',
        'status',
        'approval_status',
        'rejection_reason',
        'approved_by',
        'approved_at',
        'level',
        'language',
        'prerequisites',
        'duration',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'price'         => 'decimal:2',
            'is_free'       => 'boolean',
            'prerequisites' => 'array',
            'published_at'  => 'datetime',
            'approved_at'   => 'datetime',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function modules(): HasMany
    {
        return $this->hasMany(CourseModule::class)->orderBy('order');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'enrollments')
                    ->withPivot('status', 'progress', 'enrolled_at', 'expires_at')
                    ->withTimestamps();
    }

    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function attendance(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    // ── Scopes ─────────────────────────────────────────────────────

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeFree($query)
    {
        return $query->where('is_free', true);
    }

    public function scopeByLevel($query, string $level)
    {
        return $query->where('level', $level);
    }

    public function scopeByLanguage($query, string $language)
    {
        return $query->where('language', $language);
    }
}
