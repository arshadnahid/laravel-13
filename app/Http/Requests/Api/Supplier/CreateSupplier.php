<?php

namespace App\Http\Requests\Api\Supplier;

use App\DTOs\Supplier\SupplierCreateDTO;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateSupplier extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            // Also counts soft-deleted suppliers, matching the DB unique index
            'email' => ['required', 'email', 'max:255', 'unique:suppliers,email'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'description' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'logo_url' => ['nullable', 'url', 'max:255'],
        ];
    }

    public function toDTO(): SupplierCreateDTO
    {
        return new SupplierCreateDTO(
            name: $this->validated('name'),
            email: $this->validated('email'),
            phoneNumber: $this->validated('phone_number'),
            description: $this->validated('description'),
            address: $this->validated('address'),
            website: $this->validated('website'),
            logoUrl: $this->validated('logo_url'),
        );
    }
}
