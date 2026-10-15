<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDemoSyncRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'demo_id' => ['required', 'uuid'],
            'step' => ['required', 'string', Rule::in([
                'inlämnad',
                'skrapning',
                'kittning',
                'målning',
                'klar',
            ])],
            'notes' => ['present', 'array', 'max:20'],
            'notes.*.id' => ['required', 'string', 'max:40'],
            'notes.*.text' => ['required', 'string', 'max:280'],
            'notes.*.meta' => ['nullable', 'string', 'max:80'],
        ];
    }
}
