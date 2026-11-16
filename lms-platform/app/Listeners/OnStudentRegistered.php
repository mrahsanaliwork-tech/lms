<?php

namespace App\Listeners;

use App\Events\StudentRegistered;

class OnStudentRegistered extends ProcessAutomationTrigger
{
    protected function trigger(): string { return 'student.registered'; }

    protected function buildContext(object $event): array
    {
        /** @var StudentRegistered $event */
        return [
            'user'       => $event->user,
            'user_id'    => $event->user->id,
            'user_email' => $event->user->email,
            'user_name'  => $event->user->name,
        ];
    }
}
