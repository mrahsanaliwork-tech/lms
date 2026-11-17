<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class StripeWebhookController extends Controller
{
    public function __construct(private readonly StripeService $stripe) {}

    public function handle(Request $request)
    {
        $payload   = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature', '');

        try {
            $event = $this->stripe->constructEvent($payload, $sigHeader);
        } catch (RuntimeException $e) {
            Log::warning('Stripe webhook rejected', ['reason' => $e->getMessage()]);
            return response('Webhook signature verification failed.', 400);
        }

        match ($event['type']) {
            'checkout.session.completed' => $this->handleCheckoutCompleted($event['data']['object']),
            'checkout.session.expired'   => $this->handleCheckoutExpired($event['data']['object']),
            default                      => null,
        };

        return response()->json(['received' => true]);
    }

    // ── checkout.session.completed ─────────────────────────────────

    private function handleCheckoutCompleted(array $session): void
    {
        $paymentId = $session['metadata']['payment_id'] ?? null;
        $studentId = $session['metadata']['student_id'] ?? null;
        $courseId  = $session['metadata']['course_id']  ?? null;

        if (!$paymentId || !$studentId || !$courseId) {
            Log::error('Stripe webhook: missing metadata', ['session_id' => $session['id']]);
            return;
        }

        $payment = Payment::find($paymentId);
        if (!$payment || $payment->status === 'completed') {
            return; // Already processed or not found
        }

        DB::transaction(function () use ($session, $payment, $studentId, $courseId) {
            // Update payment record
            $payment->update([
                'status'         => 'completed',
                'transaction_id' => $session['payment_intent'] ?? $session['id'],
                'paid_at'        => now(),
            ]);

            // Increment coupon used_count
            if ($payment->coupon_id) {
                Coupon::where('id', $payment->coupon_id)->increment('used_count');
            }

            // Auto-enroll student
            Enrollment::firstOrCreate(
                ['student_id' => $studentId, 'course_id' => $courseId],
                [
                    'enrolled_at' => now(),
                    'status'      => 'active',
                    'progress'    => 0,
                ]
            );

            Log::info('Stripe payment completed + enrolled', [
                'payment_id' => $payment->id,
                'student_id' => $studentId,
                'course_id'  => $courseId,
            ]);
        });
    }

    // ── checkout.session.expired ───────────────────────────────────

    private function handleCheckoutExpired(array $session): void
    {
        $paymentId = $session['metadata']['payment_id'] ?? null;
        if (!$paymentId) return;

        Payment::where('id', $paymentId)
            ->where('status', 'pending')
            ->update(['status' => 'failed']);
    }
}
