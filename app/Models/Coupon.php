<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'description',
        'type',
        'value',
        'max_discount',
        'min_order_amount',
        'usage_limit',
        'used_count',
        'per_user_limit',
        'course_id',
        'is_active',
        'starts_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'value'            => 'decimal:2',
            'max_discount'     => 'decimal:2',
            'min_order_amount' => 'decimal:2',
            'is_active'        => 'boolean',
            'starts_at'        => 'datetime',
            'expires_at'       => 'datetime',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    // ── Business logic ─────────────────────────────────────────────

    public function isValid(float $orderAmount, int $courseId, int $studentId): bool
    {
        if (!$this->is_active) return false;
        if ($this->starts_at  && $this->starts_at->isFuture())  return false;
        if ($this->expires_at && $this->expires_at->isPast())   return false;
        if ($orderAmount < $this->min_order_amount) return false;
        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) return false;
        if ($this->course_id !== null && $this->course_id !== $courseId) return false;

        // Per-user limit
        if ($this->per_user_limit > 0) {
            $userUsage = Payment::where('coupon_id', $this->id)
                ->whereHas('student', fn ($q) => $q->where('user_id', $studentId))
                ->where('status', 'completed')
                ->count();
            if ($userUsage >= $this->per_user_limit) return false;
        }

        return true;
    }

    public function calculateDiscount(float $amount): float
    {
        if ($this->type === 'fixed') {
            return min((float) $this->value, $amount);
        }

        // percent
        $discount = $amount * ($this->value / 100);
        if ($this->max_discount !== null) {
            $discount = min($discount, (float) $this->max_discount);
        }

        return round($discount, 2);
    }
}
