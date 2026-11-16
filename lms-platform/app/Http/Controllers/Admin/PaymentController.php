<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with(['student.user', 'course', 'coupon'])
            ->latest('created_at');

        if ($s = $request->search) {
            $query->where(function ($q) use ($s) {
                $q->where('transaction_id', 'like', "%{$s}%")
                  ->orWhereHas('student.user', fn ($q) => $q->where('name', 'like', "%{$s}%"));
            });
        }

        if ($status = $request->status) {
            $query->where('status', $status);
        }

        $payments = $query->paginate(25)->through(fn ($p) => [
            'id'              => $p->id,
            'student'         => $p->student?->user?->name,
            'course'          => $p->course?->title,
            'amount'          => (float) $p->amount,
            'discount_amount' => (float) $p->discount_amount,
            'currency'        => strtoupper($p->currency),
            'status'          => $p->status,
            'payment_method'  => $p->payment_method,
            'transaction_id'  => $p->transaction_id,
            'coupon_code'     => $p->coupon?->code,
            'paid_at'         => $p->paid_at?->toDateTimeString(),
            'created_at'      => $p->created_at->toDateTimeString(),
        ]);

        $stats = [
            'total_revenue' => Payment::completed()->sum('amount'),
            'total_count'   => Payment::completed()->count(),
            'pending_count' => Payment::pending()->count(),
            'refunded'      => Payment::refunded()->sum('amount'),
        ];

        return Inertia::render('Admin/Payments/Index', compact('payments', 'stats'));
    }
}
