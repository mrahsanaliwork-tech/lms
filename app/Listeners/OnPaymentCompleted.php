<?php

namespace App\Listeners;

use App\Events\PaymentCompleted;

class OnPaymentCompleted extends ProcessAutomationTrigger
{
    protected function trigger(): string { return 'payment.completed'; }

    protected function buildContext(object $event): array
    {
        /** @var PaymentCompleted $event */
        $payment = $event->payment;
        return [
            'user'       => $payment->student?->user,
            'payment'    => $payment,
            'payment_id' => $payment->id,
            'amount'     => $payment->amount,
            'course'     => $payment->course,
            'course_id'  => $payment->course_id,
        ];
    }
}
