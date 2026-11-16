<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseModule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CourseModuleController extends Controller
{
    public function store(Request $request, Course $course): RedirectResponse
    {
        $data = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status'      => ['in:active,inactive'],
        ]);

        $data['course_id'] = $course->id;
        $data['order']     = $course->modules()->max('order') + 1;

        $course->modules()->create($data);

        return back()->with('success', 'Module added.');
    }

    public function update(Request $request, CourseModule $module): RedirectResponse
    {
        $data = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status'      => ['in:active,inactive'],
        ]);

        $module->update($data);

        return back()->with('success', 'Module updated.');
    }

    public function destroy(CourseModule $module): RedirectResponse
    {
        $module->delete();

        return back()->with('success', 'Module deleted.');
    }

    public function reorder(Request $request, Course $course): JsonResponse
    {
        $request->validate([
            'modules'          => ['required', 'array'],
            'modules.*.id'     => ['required', 'integer'],
            'modules.*.order'  => ['required', 'integer'],
        ]);

        foreach ($request->modules as $item) {
            $course->modules()->where('id', $item['id'])->update(['order' => $item['order']]);
        }

        return response()->json(['ok' => true]);
    }
}
