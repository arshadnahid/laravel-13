<?php

namespace App\Repositories\Supplier\SupplierInterface;

use App\DTOs\Supplier\SupplierCreateDTO;
use App\Models\Supplier;

interface SupplierInterface
{
    public function getAllSuppliers();
    public function getSupplierById(string $id);

    /**
     * Insert a supplier from the request DTO plus the service-generated slug.
     */
    public function createSupplier(SupplierCreateDTO $dto, string $slug): Supplier;

    public function updateSupplier(string $id, array $data);
    public function deleteSupplier(string $id);

    /**
     * Whether the slug is taken, including by soft-deleted suppliers.
     */
    public function slugExists(string $slug): bool;
}
