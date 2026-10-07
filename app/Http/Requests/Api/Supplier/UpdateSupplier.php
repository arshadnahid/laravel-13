<?php

namespace App\Http\Requests\Api\Supplier;

use App\DTOs\Supplier\SupplierUpdateDTO;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSupplier extends FormRequest
{
    /**
     * Partial update: only the sent fields are validated and changed.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            // Ignores the supplier itself; still counts soft-deleted ones, matching the DB unique index
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('suppliers', 'email')->ignore($this->route('id'))],
            'phone_number' => ['sometimes', 'nullable', 'string', 'max:20'],
            'description' => ['sometimes', 'nullable', 'string', 'max:255'],
            'address' => ['sometimes', 'nullable', 'string', 'max:255'],
            'website' => ['sometimes', 'nullable', 'url', 'max:255'],
            'logo_url' => ['sometimes', 'nullable', 'url', 'max:255'],
        ];
    }

    public function toDTO(): SupplierUpdateDTO
    {
        return new SupplierUpdateDTO($this->validated());
    }
}
