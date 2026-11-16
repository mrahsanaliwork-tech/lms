<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCourseRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title'           => ['required', 'string', 'max:255'],
            'description'     => ['nullable', 'string'],
            'instructor_id'   => ['nullable', 'exists:instructors,id'],
            'category_id'     => ['required', 'exists:categories,id'],
            'thumbnail'       => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'price'           => ['required', 'numeric', 'min:0'],
            'is_free'         => ['boolean'],
            'level'           => ['required', 'in:beginner,intermediate,advanced'],
            'language'        => ['required', 'string', 'max:10'],
            'prerequisites'   => ['nullable', 'array'],
            'prerequisites.*' => ['string', 'max:255'],
            'duration'        => ['nullable', 'integer', 'min:1'],
            'status'          => ['required', 'in:draft,published,archived'],
        ];
    }
}
