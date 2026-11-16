<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Student;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use RuntimeException;

class PaymentController extends Controller
{
    public function __construct(private readonly StripeService $stripe) {}

    private function student(): Student
    {
        return Student::where('user_id', Auth::id())->firstOrFail();
    }

    // ── Checkout page ──────────────────────────────────────────────

    public function checkout(Course $course)
    {
        abort_if($course->is_free, 404);
        abort_if($course->status !== 'published', 404);

        $student    = $this->student();
        $alreadyEnrolled = Enrollment::where('student_id', $student->id)
            ->where('course_id', $course->id)
            ->where('status', 'active')
            ->exists();

        if ($alreadyEnrolled) {
            return redirect()->route('student.courses.learn', $course->id)
                ->with('info', 'You are already enrolled in this course.');
        }

        return Inertia::render('Student/Checkout', [
            'course' => [
                'id'          => $course->id,
                'title'       => $course->title,
                'description' => $course->description,
                'price'       => (float) $course->price,
                'thumbnail'   => $course->thumbnail ? asset('storage/' . $course->thumbnail) : null,
                'instructor'  => $course->instructor?->user?->name,
            ],
        ]);
    }

    // ── Apply coupon (AJAX) ────────────────────────────────────────

    public function applyCoupon(Request $request, Course $course)
    {
        $request->validate(['code' => 'required|string']);

        $student = $this->student();
        $coupon  = Coupon::where('code', strtoupper(trim($request->code)))->first();

        if (!$coupon || !$coupon->isValid((float) $course->price, $course->id, $student->user_id)) {
            return response()->json(['error' => 'Invalid or expired coupon code.'], 422);
        }

        $discount    = $coupon->calculateDiscount((float) $course->price);
        $finalAmount = max(0, (float) $course->price - $discount);

        return response()->json([
            'coupon_id'    => $coupon->id,
            'code'         => $coupon->code,
            'discount'     => $discount,
            'final_amount' => $finalAmount,
        ]);
    }

    // ── Create Stripe session ──────────────────────────────────────

    public function createSession(Request $request, Course $course)
    {
        abort_if($course->is_free, 422);

        $data = $request->validate([
            'coupon_id' => 'nullable|integer|exists:coupons,id',
        ]);

        $student  = $this->student();
        $price    = (float) $course->price;
        $discount = 0;
        $coupon   = null;

        if (!empty($data['coupon_id'])) {
            $coupon = Coupon::findOrFail($data['coupon_id']);
            if ($coupon->isValid($price, $course->id, $student->user_id)) {
                $discount = $coupon->calculateDiscount($price);
            }
        }

        $finalAmount = max(0.50, $price - $discount); // Stripe minimum $0.50
        $amountCents = (int) round($finalAmount * 100);

        try {
            // Create a pending payment record first
            $payment = Payment::create([
                'student_id'     => $student->id,
                'course_id'      => $course->id,
                'amount'         => $finalAmount,
                'currency'       => 'usd',
                'payment_method' => 'stripe',
                'transaction_id' => 'pending_' . uniqid(),
                'coupon_id'      => $coupon?->id,
                'discount_amount'=> $discount,
                'status'         => 'pending',
            ]);

            $session = $this->stripe->createCheckoutSession(
                courseId:    $course->id,
                courseTitle: $course->title,
                amountCents: $amountCents,
                currency:    'usd',
                successUrl:  route('payment.success') . '?session_id={CHECKOUT_SESSION_ID}',
                cancelUrl:   route('payment.cancel', $course->id),
                metadata:    [
                    'payment_id' => $payment->id,
                    'student_id' => $student->id,
                    'course_id'  => $course->id,
                ],
            );

            // Store the session ID on the payment record
            $payment->update(['stripe_session_id' => $session['id']]);

            return response()->json(['url' => $session['url']]);

        } catch (RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    // ── Success redirect ───────────────────────────────────────────

    public function success(Request $request)
    {
        $sessionId = $request->query('session_id');
        $payment   = Payment::where('stripe_session_id', $sessionId)->first();

        return Inertia::render('Student/PaymentSuccess', [
            'payment' => $payment ? [
                'course_title' => $payment->course?->title,
                'amount'       => (float) $payment->amount,
                'status'       => $payment->status,
            ] : null,
        ]);
    }

    // ── Cancel redirect ────────────────────────────────────────────

    public function cancel(Course $course)
    {
        return redirect()->route('student.checkout', $course->id)
            ->with('info', 'Payment was cancelled. You can try again anytime.');
    }

    // ── Payment history ────────────────────────────────────────────

    public function history()
    {
        $student = $this->student();

        $payments = Payment::with(['course', 'coupon'])
            ->where('student_id', $student->id)
            ->latest('created_at')
            ->paginate(20)
            ->through(fn ($p) => [
                'id'              => $p->id,
                'course'          => $p->course?->title,
                'amount'          => (float) $p->amount,
                'discount_amount' => (float) $p->discount_amount,
                'currency'        => $p->currency,
                'status'          => $p->status,
                'coupon_code'     => $p->coupon?->code,
                'paid_at'         => $p->paid_at?->toDateTimeString(),
                'created_at'      => $p->created_at->toDateTimeString(),
            ]);

        return Inertia::render('Student/PaymentHistory', compact('payments'));
    }
}
