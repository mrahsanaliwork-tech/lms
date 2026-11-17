<?php

namespace App\Events;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AssignmentSubmitted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly User                 $student,
        public readonly Assignment           $assignment,
        public readonly AssignmentSubmission $submission,
    ) {}
}
