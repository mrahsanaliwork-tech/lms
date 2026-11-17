<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CertificateIssued extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $courseTitle,
        public readonly int    $courseId,
        public readonly string $certificateUrl,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Certificate Issued: ' . $this->courseTitle)
            ->greeting('Congratulations, ' . $notifiable->name . '!')
            ->line('You have successfully completed **' . $this->courseTitle . '** and earned your certificate.')
            ->action('Download Certificate', url($this->certificateUrl))
            ->line('Share your achievement with the world!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'            => 'certificate_issued',
            'title'           => 'Certificate earned!',
            'message'         => 'You completed ' . $this->courseTitle,
            'url'             => $this->certificateUrl,
            'icon'            => 'badge-check',
            'course_id'       => $this->courseId,
            'certificate_url' => $this->certificateUrl,
        ];
    }
}
