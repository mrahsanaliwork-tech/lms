<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreLessonRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'module_id'   => ['required', 'exists:course_modules,id'],
            'title'       => ['required', 'string', 'max:255'],
            'type'        => ['required', 'in:video,pdf,text'],
            'content'     => ['nullable', 'string'],
            'file'        => ['nullable', 'file', 'max:102400', 'mimes:mp4,webm,pdf'],
            'duration'    => ['nullable', 'integer', 'min:1'],
            'order'       => ['nullable', 'integer', 'min:0'],
            'is_free'     => ['boolean'],
            'status'      => ['required', 'in:active,inactive'],
        ];
    }
}
