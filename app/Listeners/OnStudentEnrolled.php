<?php

namespace App\Listeners;

use App\Events\StudentEnrolled;

class OnStudentEnrolled extends ProcessAutomationTrigger
{
    protected function trigger(): string { return 'student.enrolled'; }

    protected function buildContext(object $event): array
    {
        /** @var StudentEnrolled $event */
        return [
            'user'        => $event->user,
            'course'      => $event->course,
            'user_id'     => $event->user->id,
            'course_id'   => $event->course->id,
            'course_title'=> $event->course->title,
        ];
    }
}
