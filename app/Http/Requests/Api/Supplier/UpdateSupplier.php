<?php

namespace App\Http\Requests\Api\Supplier;

use App\DTOs\Supplier\SupplierUpdateDTO;
use App\Rules\NotUsedBySoftDeletedSupplier;
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
            // Ignores the supplier itself; soft-deleted rows still hold the DB unique index, so they get their own message
            //Rule::unique(...)->whereNull('deleted_at') checks active suppliers only. If it finds a match, it fails with Laravel's standard "The email has already been taken."
            //NotUsedBySoftDeletedSupplier checks soft-deleted suppliers only. If it finds a match, it fails with the specific "currently inactive (soft deleted)" message.
            'email' => ['bail', 'sometimes', 'required', 'email', 'max:255', Rule::unique('suppliers', 'email')->whereNull('deleted_at')->ignore($this->route('id')), new NotUsedBySoftDeletedSupplier('email', $this->route('id'))],
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
