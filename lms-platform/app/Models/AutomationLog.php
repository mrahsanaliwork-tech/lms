<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutomationLog extends Model
{
    protected $fillable = [
        'workflow_id',
        'trigger',
        'context',
        'status',
        'error',
    ];

    protected function casts(): array
    {
        return [
            'context' => 'array',
        ];
    }

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(AutomationWorkflow::class);
    }
}
