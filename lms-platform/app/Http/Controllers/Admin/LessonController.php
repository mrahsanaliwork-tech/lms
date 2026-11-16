<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreLessonRequest;
use App\Models\CourseModule;
use App\Models\Lesson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LessonController extends Controller
{
    public function store(StoreLessonRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_free'] = $request->boolean('is_free');

        $module = CourseModule::findOrFail($data['module_id']);
        if (!isset($data['order'])) {
            $data['order'] = $module->lessons()->max('order') + 1;
        }

        if ($request->hasFile('file')) {
            $path = $request->file('file')->store("lessons/{$module->course_id}", 'public');
            $data['content_url'] = $path;
        }

        unset($data['content'], $data['file']);

        Lesson::create($data);

        return back()->with('success', 'Lesson added.');
    }

    public function update(Request $request, Lesson $lesson): RedirectResponse
    {
        $data = $request->validate([
            'title'    => ['required', 'string', 'max:255'],
            'type'     => ['required', 'in:video,pdf,text'],
            'file'     => ['nullable', 'file', 'max:102400', 'mimes:mp4,webm,pdf'],
            'duration' => ['nullable', 'integer', 'min:1'],
            'is_free'  => ['boolean'],
            'status'   => ['required', 'in:active,inactive'],
        ]);

        $data['is_free'] = $request->boolean('is_free');

        if ($request->hasFile('file')) {
            if ($lesson->content_url) Storage::disk('public')->delete($lesson->content_url);
            $courseId = $lesson->module->course_id;
            $data['content_url'] = $request->file('file')->store("lessons/{$courseId}", 'public');
        }

        unset($data['file']);
        $lesson->update($data);

        return back()->with('success', 'Lesson updated.');
    }

    public function destroy(Lesson $lesson): RedirectResponse
    {
        if ($lesson->content_url) Storage::disk('public')->delete($lesson->content_url);
        $lesson->delete();

        return back()->with('success', 'Lesson deleted.');
    }

    public function reorder(Request $request, CourseModule $module): JsonResponse
    {
        $request->validate([
            'lessons'         => ['required', 'array'],
            'lessons.*.id'    => ['required', 'integer'],
            'lessons.*.order' => ['required', 'integer'],
        ]);

        foreach ($request->lessons as $item) {
            $module->lessons()->where('id', $item['id'])->update(['order' => $item['order']]);
        }

        return response()->json(['ok' => true]);
    }
}
