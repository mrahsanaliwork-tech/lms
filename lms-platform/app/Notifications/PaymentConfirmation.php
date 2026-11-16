<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentConfirmation extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Payment $payment) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function broadcastType(): string
    {
        return 'payment_confirmation';
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Payment Confirmed – ' . config('app.name'))
            ->greeting('Hi ' . $notifiable->name . '!')
            ->line('Your payment of **$' . number_format($this->payment->amount, 2) . '** has been received.')
            ->line('Course: ' . ($this->payment->course->title ?? 'N/A'));

        if ($this->payment->discount_amount > 0) {
            $mail->line('Discount applied: -$' . number_format($this->payment->discount_amount, 2));
        }

        return $mail
            ->action('View Receipt', url('/student/payments'))
            ->line('Thank you for your purchase!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'    => 'payment_confirmation',
            'title'   => 'Payment confirmed',
            'message' => '$' . number_format($this->payment->amount, 2) . ' charged for ' . ($this->payment->course->title ?? 'course'),
            'url'     => '/student/payments',
            'icon'    => 'credit-card',
        ];
    }
}
