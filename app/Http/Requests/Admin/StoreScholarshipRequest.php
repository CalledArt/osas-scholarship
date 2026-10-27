<?php

namespace App\Http\Requests\Admin;

use App\Enums\ScholarshipType;
use App\Models\Scholarship;
use App\Support\ScholarshipPresets;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Validator;

class StoreScholarshipRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Scholarship::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'type' => ['required', new Enum(ScholarshipType::class)],
            'preset' => ['nullable', Rule::in(array_keys(ScholarshipPresets::ALL))],
            'slots' => ['required', 'integer', 'min:1'],
            'deadline' => ['nullable', 'date', 'after:today'],
            'required_documents' => ['nullable', 'array'],
            'required_documents.*.name' => ['required', 'string', 'max:255'],
            'required_documents.*.description' => ['nullable', 'string', 'max:1000'],
            'required_documents.*.sample' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'required_documents.*.sample' => 'sample',
        ];
    }

    /**
     * Refuse a preset, or a title, while another posting of it is still open for applications.
     *
     * The controller checks the preset again under a lock, since two posts of
     * one preset sent at the same moment both pass this check. A title is
     * checked only here: the form disables its button once sent, so a double
     * click cannot post it twice. A refused preset skips the title check,
     * whose message would only repeat it.
     *
     * @return array<callable(Validator): void>
     */
    public function after(): array
    {
        return [function (Validator $validator): void {
            $preset = $this->input('preset');

            if (! $validator->errors()->has('preset') && filled($preset) && Scholarship::openFromPreset($preset)->exists()) {
                $validator->errors()->add('preset', self::presetAlreadyOpenMessage($preset));

                return;
            }

            $title = $this->input('title');

            if (! $validator->errors()->has('title') && is_string($title) && Scholarship::hasOpenPostingTitled($title)) {
                $validator->errors()->add('title', __('A scholarship titled “:title” is still open for applications. Close it before posting it again.', [
                    'title' => Str::squish($title),
                ]));
            }
        }];
    }

    /**
     * Why a preset cannot be posted again yet.
     */
    public static function presetAlreadyOpenMessage(string $preset): string
    {
        return __('A :title posting is already open for applications. Close it before posting another one.', [
            'title' => ScholarshipPresets::ALL[$preset]['title'],
        ]);
    }
}
