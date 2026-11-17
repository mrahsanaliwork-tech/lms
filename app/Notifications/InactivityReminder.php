<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InactivityReminder extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly int    $daysSinceActive,
        public readonly string $courseTitle = '',
        public readonly int    $courseId    = 0,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('We miss you, ' . $notifiable->name . '!')
            ->greeting('Hey ' . $notifiable->name . ',')
            ->line("You haven't been active for {$this->daysSinceActive} days. Don't let your momentum fade!");

        if ($this->courseTitle) {
            $mail->line("Pick up where you left off in **{$this->courseTitle}**.")
                 ->action('Continue Learning', url('/student/courses/' . $this->courseId));
        } else {
            $mail->line('Browse your enrolled courses and keep moving forward.')
                 ->action('My Courses', url('/student/courses'));
        }

        return $mail->line('Every day is a chance to learn something new.');
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}
