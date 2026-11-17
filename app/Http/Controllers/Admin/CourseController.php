<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCourseRequest;
use App\Http\Requests\Admin\UpdateCourseRequest;
use App\Models\Category;
use App\Models\Course;
use App\Models\Instructor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CourseController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Course::with(['instructor.user', 'category'])
            ->withCount('enrollments')
            ->when($request->search, fn ($q, $s) => $q->where('title', 'like', "%{$s}%"))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->approval, fn ($q, $a) => $q->where('approval_status', $a))
            ->when($request->category, fn ($q, $c) => $q->where('category_id', $c))
            ->latest();

        $courses = $query->paginate(15)->withQueryString()->through(fn ($c) => [
            'id'              => $c->id,
            'title'           => $c->title,
            'slug'            => $c->slug,
            'thumbnail'       => $c->thumbnail ? Storage::url($c->thumbnail) : null,
            'instructor'      => $c->instructor?->user?->name,
            'category'        => $c->category?->name,
            'price'           => $c->is_free ? 'Free' : number_format($c->price, 2),
            'is_free'         => $c->is_free,
            'level'           => $c->level,
            'status'          => $c->status,
            'approval_status' => $c->approval_status,
            'enrollments'     => $c->enrollments_count,
            'published_at'    => $c->published_at?->format('M d, Y'),
            'created_at'      => $c->created_at->format('M d, Y'),
        ]);

        return Inertia::render('Admin/Courses/Index', [
            'courses'    => $courses,
            'categories' => Category::active()->roots()->get(['id', 'name']),
            'filters'    => $request->only('search', 'status', 'approval', 'category'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Courses/Create', [
            'instructors' => Instructor::with('user')->verified()->get()->map(fn ($i) => [
                'id'   => $i->id,
                'name' => $i->user?->name,
            ]),
            'categories'  => Category::active()->get(['id', 'name', 'parent_id']),
        ]);
    }

    public function store(StoreCourseRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = Str::slug($data['title']) . '-' . Str::random(5);
        $data['is_free'] = $request->boolean('is_free');

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')->store('thumbnails', 'public');
        }

        $course = Course::create($data);

        return redirect()->route('admin.courses.show', $course)
            ->with('success', 'Course created.');
    }

    public function show(Course $course): Response
    {
        $course->load([
            'instructor.user',
            'category',
            'modules' => fn ($q) => $q->withCount('lessons')->with(['lessons' => fn ($q) => $q->orderBy('order')]),
        ]);

        $stats = [
            'enrollments' => $course->enrollments()->count(),
            'completed'   => $course->enrollments()->where('status', 'completed')->count(),
            'modules'     => $course->modules->count(),
            'lessons'     => $course->modules->sum('lessons_count'),
        ];

        return Inertia::render('Admin/Courses/Show', [
            'course' => [
                'id'               => $course->id,
                'title'            => $course->title,
                'slug'             => $course->slug,
                'description'      => $course->description,
                'thumbnail'        => $course->thumbnail ? Storage::url($course->thumbnail) : null,
                'instructor'       => $course->instructor?->user?->name,
                'instructor_id'    => $course->instructor_id,
                'category'         => $course->category?->name,
                'price'            => $course->price,
                'is_free'          => $course->is_free,
                'level'            => $course->level,
                'language'         => $course->language,
                'status'           => $course->status,
                'approval_status'  => $course->approval_status,
                'rejection_reason' => $course->rejection_reason,
                'published_at'     => $course->published_at?->format('M d, Y'),
                'created_at'       => $course->created_at->format('M d, Y'),
            ],
            'stats'   => $stats,
            'modules' => $course->modules->map(fn ($m) => [
                'id'          => $m->id,
                'title'       => $m->title,
                'description' => $m->description,
                'order'       => $m->order,
                'status'      => $m->status,
                'lessons'     => $m->lessons->map(fn ($l) => [
                    'id'          => $l->id,
                    'title'       => $l->title,
                    'type'        => $l->type,
                    'duration'    => $l->duration,
                    'order'       => $l->order,
                    'is_free'     => $l->is_free,
                    'status'      => $l->status,
                    'content_url' => $l->content_url ? Storage::url($l->content_url) : null,
                ]),
            ]),
        ]);
    }

    public function edit(Course $course): Response
    {
        $course->load('instructor.user', 'category');

        return Inertia::render('Admin/Courses/Edit', [
            'course' => [
                'id'            => $course->id,
                'title'         => $course->title,
                'description'   => $course->description,
                'instructor_id' => $course->instructor_id,
                'category_id'   => $course->category_id,
                'thumbnail'     => $course->thumbnail ? Storage::url($course->thumbnail) : null,
                'price'         => $course->price,
                'is_free'       => $course->is_free,
                'level'         => $course->level,
                'language'      => $course->language,
                'prerequisites' => $course->prerequisites ?? [],
                'duration'      => $course->duration,
                'status'        => $course->status,
            ],
            'instructors' => Instructor::with('user')->verified()->get()->map(fn ($i) => [
                'id'   => $i->id,
                'name' => $i->user?->name,
            ]),
            'categories' => Category::active()->get(['id', 'name', 'parent_id']),
        ]);
    }

    public function update(UpdateCourseRequest $request, Course $course): RedirectResponse
    {
        $data = $request->validated();
        $data['is_free'] = $request->boolean('is_free');

        if ($request->hasFile('thumbnail')) {
            if ($course->thumbnail) Storage::disk('public')->delete($course->thumbnail);
            $data['thumbnail'] = $request->file('thumbnail')->store('thumbnails', 'public');
        } else {
            unset($data['thumbnail']);
        }

        $course->update($data);

        return redirect()->route('admin.courses.show', $course)
            ->with('success', 'Course updated.');
    }

    public function destroy(Course $course): RedirectResponse
    {
        if ($course->thumbnail) Storage::disk('public')->delete($course->thumbnail);
        $course->delete();

        return redirect()->route('admin.courses.index')
            ->with('success', 'Course removed.');
    }

    // ── Status management ──────────────────────────────────────────────

    public function publish(Course $course): RedirectResponse
    {
        $course->update([
            'status'       => 'published',
            'published_at' => $course->published_at ?? now(),
        ]);

        return back()->with('success', 'Course published.');
    }

    public function unpublish(Course $course): RedirectResponse
    {
        $course->update(['status' => 'draft']);

        return back()->with('success', 'Course moved to draft.');
    }

    public function archive(Course $course): RedirectResponse
    {
        $course->update(['status' => 'archived']);

        return back()->with('success', 'Course archived.');
    }

    // ── Approval system ────────────────────────────────────────────────

    public function approve(Course $course): RedirectResponse
    {
        $course->update([
            'approval_status' => 'approved',
            'approved_by'     => Auth::id(),
            'approved_at'     => now(),
            'rejection_reason'=> null,
        ]);

        return back()->with('success', 'Course approved.');
    }

    public function reject(Request $request, Course $course): RedirectResponse
    {
        $request->validate(['reason' => ['required', 'string', 'max:1000']]);

        $course->update([
            'approval_status'  => 'rejected',
            'rejection_reason' => $request->reason,
        ]);

        return back()->with('success', 'Course rejected.');
    }
}
