<?php

namespace App\Notifications;

use App\Mail\AssignmentGraded as AssignmentGradedMail;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class AssignmentGraded extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Assignment $assignment,
        public readonly AssignmentSubmission $submission,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function broadcastType(): string
    {
        return 'assignment_graded';
    }

    public function toMail(object $notifiable): AssignmentGradedMail
    {
        return new AssignmentGradedMail($this->assignment, $this->submission);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'          => 'assignment_graded',
            'title'         => 'Assignment graded: ' . $this->assignment->title,
            'message'       => 'Score: ' . $this->submission->score . '/' . $this->assignment->max_score,
            'url'           => '/student/assignments',
            'icon'          => 'clipboard-check',
            'assignment_id' => $this->assignment->id,
        ];
    }
}
