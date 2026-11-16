<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $userId = $this->route('student')?->user_id;

        return [
            'name'            => ['required', 'string', 'max:255'],
            'email'           => ['required', 'email', 'max:255', "unique:users,email,{$userId}"],
            'phone'           => ['nullable', 'string', 'max:20'],
            'organization_id' => ['nullable', 'exists:organizations,id'],
            'batch'           => ['nullable', 'string', 'max:100'],
            'group'           => ['nullable', 'string', 'max:100'],
            'enrollment_date' => ['nullable', 'date'],
            'status'          => ['required', 'in:active,inactive,graduated,suspended'],
        ];
    }
}
