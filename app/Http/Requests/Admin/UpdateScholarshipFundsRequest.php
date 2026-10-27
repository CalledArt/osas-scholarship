<?php

namespace App\Http\Requests\Admin;

use App\Support\Peso;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateScholarshipFundsRequest extends FormRequest
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
     * Whole centavos only, so every share is computed from an exact amount.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'total_funds' => ['required', 'numeric', 'decimal:0,2', 'min:1', 'max:9999999999.99'],
        ];
    }

    /**
     * Give every student who passed at least a centavo, or the allocation
     * sheet would list them for ₱0.00.
     *
     * @return array<callable(Validator): void>
     */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->has('total_funds')) {
                return;
            }

            $passedCount = $this->route('scholarship')->applications()->approved()->count();

            if (Peso::toCentavos($this->input('total_funds')) < $passedCount) {
                $validator->errors()->add('total_funds', __('The total funds must give each of the :count students who passed at least ₱0.01.', [
                    'count' => $passedCount,
                ]));
            }
        }];
    }
}
