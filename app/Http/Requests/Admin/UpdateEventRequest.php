<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_admin === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $eventId = $this->route('event')?->id;

        return [
            'title'         => ['required', 'string', 'max:255'],
            'slug'          => ['required', 'string', 'max:255', 'regex:/^[a-z0-9-]+$/', Rule::unique('events', 'slug')->ignore($eventId)->where(function ($query) {
                $date = Carbon::parse($this->input('event_date'));
                return $query->whereYear('event_date', $date->year)->whereMonth('event_date', $date->month)->whereDay('event_date', $date->day);
            })],
            'description'   => ['nullable', 'string'],
            'event_date'    => ['required', 'date'],
            'is_private'    => ['boolean'],
            'has_watermark' => ['boolean'],
            'status'        => ['required', 'in:draft,published'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'slug.regex'  => 'Lo slug può contenere solo lettere minuscole, numeri e trattini.',
            'slug.unique' => 'Questo slug è già in uso. Scegline un altro.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_private'    => $this->boolean('is_private'),
            'has_watermark' => $this->boolean('has_watermark'),
        ]);
    }
}
