<?php

namespace App\DTOs\Supplier;

class SupplierFilterDTO
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $email = null,
        public readonly ?string $phoneNumber = null,
        public readonly ?string $address = null,
        public readonly int $perPage = 15,
    ) {}
}
