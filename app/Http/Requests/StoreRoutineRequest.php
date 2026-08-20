<?php

namespace App\Http\Requests;

use App\Enums\RoutineFrequency;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoutineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'frequency' => ['required', Rule::enum(RoutineFrequency::class)],
            'scheduled_time' => ['required', 'date_format:H:i'],
            'timezone' => ['required', 'timezone:all'],
            'starts_on' => ['required', 'date_format:Y-m-d'],
            'ends_on' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:starts_on'],
            'user_id' => ['prohibited'],
        ];
    }
}
