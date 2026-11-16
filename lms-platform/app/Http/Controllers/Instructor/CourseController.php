<?php

namespace App\Http\Controllers\Instructor;

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
    private function instructor(): Instructor
    {
        return Instructor::where('user_id', Auth::id())->firstOrFail();
    }

    public function index(Request $request): Response
    {
        $instructor = $this->instructor();

        $courses = Course::where('instructor_id', $instructor->id)
            ->withCount('enrollments')
            ->when($request->search, fn ($q, $s) => $q->where('title', 'like', "%{$s}%"))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(fn ($c) => [
                'id'              => $c->id,
                'title'           => $c->title,
                'thumbnail'       => $c->thumbnail ? Storage::url($c->thumbnail) : null,
                'price'           => $c->is_free ? 'Free' : number_format($c->price, 2),
                'is_free'         => $c->is_free,
                'level'           => $c->level,
                'status'          => $c->status,
                'approval_status' => $c->approval_status,
                'enrollments'     => $c->enrollments_count,
                'published_at'    => $c->published_at?->format('M d, Y'),
                'created_at'      => $c->created_at->format('M d, Y'),
            ]);

        return Inertia::render('Instructor/Courses/Index', [
            'courses' => $courses,
            'filters' => $request->only('search', 'status'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Instructor/Courses/Create', [
            'categories' => Category::active()->get(['id', 'name', 'parent_id']),
        ]);
    }

    public function store(StoreCourseRequest $request): RedirectResponse
    {
        $instructor = $this->instructor();
        $data = $request->validated();
        $data['slug']          = Str::slug($data['title']) . '-' . Str::random(5);
        $data['is_free']       = $request->boolean('is_free');
        $data['instructor_id'] = $instructor->id;

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')->store('thumbnails', 'public');
        }

        $course = Course::create($data);

        return redirect()->route('instructor.courses.show', $course)
            ->with('success', 'Course created and submitted for review.');
    }

    public function show(Course $course): Response
    {
        $this->authorizeOwner($course);
        $course->load([
            'category',
            'modules' => fn ($q) => $q->withCount('lessons')->with(['lessons' => fn ($q) => $q->orderBy('order')]),
        ]);

        $stats = [
            'enrollments' => $course->enrollments()->count(),
            'completed'   => $course->enrollments()->where('status', 'completed')->count(),
            'modules'     => $course->modules->count(),
            'lessons'     => $course->modules->sum('lessons_count'),
        ];

        return Inertia::render('Instructor/Courses/Show', [
            'course' => [
                'id'               => $course->id,
                'title'            => $course->title,
                'description'      => $course->description,
                'thumbnail'        => $course->thumbnail ? Storage::url($course->thumbnail) : null,
                'level'            => $course->level,
                'language'         => $course->language,
                'status'           => $course->status,
                'approval_status'  => $course->approval_status,
                'rejection_reason' => $course->rejection_reason,
                'is_free'          => $course->is_free,
                'price'            => $course->price,
                'published_at'     => $course->published_at?->format('M d, Y'),
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
        $this->authorizeOwner($course);
        $course->load('category');

        return Inertia::render('Instructor/Courses/Edit', [
            'course' => [
                'id'            => $course->id,
                'title'         => $course->title,
                'description'   => $course->description,
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
            'categories' => Category::active()->get(['id', 'name']),
        ]);
    }

    public function update(UpdateCourseRequest $request, Course $course): RedirectResponse
    {
        $this->authorizeOwner($course);

        $data = $request->validated();
        $data['is_free']  = $request->boolean('is_free');
        unset($data['instructor_id']); // instructor cannot change ownership

        if ($request->hasFile('thumbnail')) {
            if ($course->thumbnail) Storage::disk('public')->delete($course->thumbnail);
            $data['thumbnail'] = $request->file('thumbnail')->store('thumbnails', 'public');
        } else {
            unset($data['thumbnail']);
        }

        // Reset approval when content changes
        $data['approval_status'] = 'pending';

        $course->update($data);

        return redirect()->route('instructor.courses.show', $course)
            ->with('success', 'Course updated and resubmitted for review.');
    }

    public function destroy(Course $course): RedirectResponse
    {
        $this->authorizeOwner($course);
        if ($course->thumbnail) Storage::disk('public')->delete($course->thumbnail);
        $course->delete();

        return redirect()->route('instructor.courses.index')->with('success', 'Course deleted.');
    }

    private function authorizeOwner(Course $course): void
    {
        $instructor = $this->instructor();
        abort_if($course->instructor_id !== $instructor->id, 403);
    }
}
