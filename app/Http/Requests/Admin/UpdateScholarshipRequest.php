<?php

namespace App\Http\Requests\Admin;

use App\Enums\ScholarshipStatus;
use App\Enums\ScholarshipType;
use App\Models\Scholarship;
use App\Support\ScholarshipPresets;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Validator;

class UpdateScholarshipRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('scholarship'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * The course limit is not editable: it was set by the preset the
     * scholarship was posted from (see ScholarshipPresets).
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'type' => ['required', new Enum(ScholarshipType::class)],
            'slots' => ['required', 'integer', 'min:1'],
            'deadline' => ['nullable', 'date'],
            'status' => ['required', new Enum(ScholarshipStatus::class)],
        ];
    }

    /**
     * Refuse to reopen a preset's posting while another of that preset is
     * open, or to leave a posting open under a title another open posting has.
     *
     * Only reopening, and for titles renaming, is checked, so a posting that
     * is already open stays editable even if an older duplicate is open
     * beside it.
     *
     * @return array<callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $scholarship = $this->route('scholarship');

                if ($validator->errors()->has('status')
                    || $scholarship->preset === null
                    || $scholarship->status === ScholarshipStatus::Open
                    || $this->input('status') !== ScholarshipStatus::Open->value) {
                    return;
                }

                if (Scholarship::openFromPreset($scholarship->preset)->exists()) {
                    $validator->errors()->add('status', __('Another :title posting is already open for applications. Close it before reopening this one.', [
                        'title' => ScholarshipPresets::ALL[$scholarship->preset]['title'] ?? $scholarship->preset,
                    ]));
                }
            },
            function (Validator $validator): void {
                $scholarship = $this->route('scholarship');
                $title = $this->input('title');

                if ($validator->errors()->hasAny(['title', 'status'])
                    || ! is_string($title)
                    || $this->input('status') !== ScholarshipStatus::Open->value) {
                    return;
                }

                $isReopening = $scholarship->status !== ScholarshipStatus::Open;
                $isRenaming = Scholarship::normalizedTitle($title) !== Scholarship::normalizedTitle($scholarship->title);

                if (($isReopening || $isRenaming) && Scholarship::hasOpenPostingTitled($title, except: $scholarship)) {
                    $validator->errors()->add('title', __('Another scholarship titled “:title” is still open for applications. Close it first, or give this one a different title.', [
                        'title' => Str::squish($title),
                    ]));
                }
            },
        ];
    }
}
