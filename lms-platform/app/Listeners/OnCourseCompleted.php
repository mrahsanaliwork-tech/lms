<?php

namespace App\Listeners;

use App\Events\CourseCompleted;

class OnCourseCompleted extends ProcessAutomationTrigger
{
    protected function trigger(): string { return 'course.completed'; }

    protected function buildContext(object $event): array
    {
        /** @var CourseCompleted $event */
        return [
            'user'         => $event->user,
            'course'       => $event->course,
            'user_id'      => $event->user->id,
            'course_id'    => $event->course->id,
            'course_title' => $event->course->title,
        ];
    }
}
