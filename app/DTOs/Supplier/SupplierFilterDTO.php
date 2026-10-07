<?php

namespace App\DTOs\Supplier;

class SupplierFilterDTO
{
    /**
     * @param  string|null  $trashed  "only" for soft-deleted suppliers, "with" for active and soft-deleted, null for active only.
     */
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $email = null,
        public readonly ?string $phoneNumber = null,
        public readonly ?string $address = null,
        public readonly ?string $trashed = null,
        public readonly int $perPage = 15,
    ) {}
}
