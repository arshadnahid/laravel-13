<?php

namespace App\Http\Requests\Api\Supplier;

use App\DTOs\Supplier\SupplierFilterDTO;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class FilterSupplier extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function toDTO(): SupplierFilterDTO
    {
        return new SupplierFilterDTO(
            name: $this->validated('name'),
            email: $this->validated('email'),
            phoneNumber: $this->validated('phone_number'),
            address: $this->validated('address'),
            perPage: (int) $this->validated('per_page', 15),
        );
    }
}
