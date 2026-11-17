<?php

namespace App\Listeners;

use App\Events\AssignmentSubmitted;

class OnAssignmentSubmitted extends ProcessAutomationTrigger
{
    protected function trigger(): string { return 'assignment.submitted'; }

    protected function buildContext(object $event): array
    {
        /** @var AssignmentSubmitted $event */
        return [
            'user'              => $event->student,
            'course'            => $event->assignment->course,
            'assignment'        => $event->assignment,
            'submission'        => $event->submission,
            'assignment_title'  => $event->assignment->title,
            'student_name'      => $event->student->name,
        ];
    }
}
