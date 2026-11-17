<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Course;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CouponController extends Controller
{
    public function index()
    {
        $coupons = Coupon::with('course')
            ->latest()
            ->paginate(25)
            ->through(fn ($c) => [
                'id'               => $c->id,
                'code'             => $c->code,
                'description'      => $c->description,
                'type'             => $c->type,
                'value'            => (float) $c->value,
                'max_discount'     => $c->max_discount ? (float) $c->max_discount : null,
                'min_order_amount' => (float) $c->min_order_amount,
                'usage_limit'      => $c->usage_limit,
                'used_count'       => $c->used_count,
                'per_user_limit'   => $c->per_user_limit,
                'course'           => $c->course?->title,
                'is_active'        => $c->is_active,
                'starts_at'        => $c->starts_at?->toDateTimeString(),
                'expires_at'       => $c->expires_at?->toDateTimeString(),
            ]);

        $courses = Course::where('status', 'published')->get(['id', 'title']);

        return Inertia::render('Admin/Coupons/Index', compact('coupons', 'courses'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code'             => 'required|string|max:50|unique:coupons,code',
            'description'      => 'nullable|string|max:255',
            'type'             => 'required|in:percent,fixed',
            'value'            => 'required|numeric|min:0.01',
            'max_discount'     => 'nullable|numeric|min:0.01',
            'min_order_amount' => 'nullable|numeric|min:0',
            'usage_limit'      => 'nullable|integer|min:1',
            'per_user_limit'   => 'nullable|integer|min:0',
            'course_id'        => 'nullable|integer|exists:courses,id',
            'is_active'        => 'boolean',
            'starts_at'        => 'nullable|date',
            'expires_at'       => 'nullable|date|after_or_equal:starts_at',
        ]);

        $data['code'] = strtoupper(trim($data['code']));

        Coupon::create($data);

        return back()->with('success', 'Coupon created.');
    }

    public function update(Request $request, Coupon $coupon)
    {
        $data = $request->validate([
            'code'             => "required|string|max:50|unique:coupons,code,{$coupon->id}",
            'description'      => 'nullable|string|max:255',
            'type'             => 'required|in:percent,fixed',
            'value'            => 'required|numeric|min:0.01',
            'max_discount'     => 'nullable|numeric|min:0.01',
            'min_order_amount' => 'nullable|numeric|min:0',
            'usage_limit'      => 'nullable|integer|min:1',
            'per_user_limit'   => 'nullable|integer|min:0',
            'course_id'        => 'nullable|integer|exists:courses,id',
            'is_active'        => 'boolean',
            'starts_at'        => 'nullable|date',
            'expires_at'       => 'nullable|date|after_or_equal:starts_at',
        ]);

        $data['code'] = strtoupper(trim($data['code']));
        $coupon->update($data);

        return back()->with('success', 'Coupon updated.');
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();
        return back()->with('success', 'Coupon deleted.');
    }
}
