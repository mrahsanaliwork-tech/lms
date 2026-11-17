<?php

namespace App\Notifications;

use App\Models\Course;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EnrollmentConfirmation extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Course $course) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function broadcastType(): string
    {
        return 'enrollment';
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Enrolled: ' . $this->course->title)
            ->greeting('Hi ' . $notifiable->name . '!')
            ->line('You have successfully enrolled in **' . $this->course->title . '**.')
            ->line('Your learning journey begins now.')
            ->action('Start Learning', url('/student/courses/' . $this->course->id))
            ->line('Good luck!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'      => 'enrollment',
            'title'     => 'Enrolled in ' . $this->course->title,
            'message'   => 'You are now enrolled. Start learning today!',
            'url'       => '/student/courses/' . $this->course->id,
            'icon'      => 'academic-cap',
            'course_id' => $this->course->id,
        ];
    }
}
