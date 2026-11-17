<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function broadcastType(): string
    {
        return 'welcome';
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Welcome to ' . config('app.name'))
            ->greeting('Welcome, ' . $notifiable->name . '!')
            ->line('Your account has been created successfully.')
            ->line('Start exploring courses and expand your knowledge.')
            ->action('Browse Courses', url('/courses'))
            ->line('Happy learning!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'    => 'welcome',
            'title'   => 'Welcome to ' . config('app.name'),
            'message' => 'Your account has been created. Start exploring courses!',
            'url'     => '/courses',
            'icon'    => 'sparkles',
        ];
    }
}
