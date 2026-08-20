<?php

namespace App\Http\Requests;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class CalendarEventsRequest extends FormRequest
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
            'start' => ['required', 'date'],
            'end' => ['required', 'date', 'after:start'],
        ];
    }

    /**
     * Add constraints that require both range boundaries.
     *
     * @return array<int, \Closure(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                if ($this->end()->lessThanOrEqualTo($this->start())) {
                    $validator->errors()->add('end', __('The calendar end must be after the start.'));

                    return;
                }

                if (abs($this->end()->diffInDays($this->start())) > 62) {
                    $validator->errors()->add('end', __('The calendar range may not exceed 62 days.'));
                }
            },
        ];
    }

    public function start(): CarbonImmutable
    {
        return CarbonImmutable::parse((string) $this->input('start'));
    }

    public function end(): CarbonImmutable
    {
        return CarbonImmutable::parse((string) $this->input('end'));
    }
}
