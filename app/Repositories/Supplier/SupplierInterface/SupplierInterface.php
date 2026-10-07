<?php

namespace App\Repositories\Supplier\SupplierInterface;

use App\DTOs\Supplier\SupplierCreateDTO;
use App\DTOs\Supplier\SupplierFilterDTO;
use App\Models\Supplier;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface SupplierInterface
{
    /**
     * Paginated suppliers, optionally filtered by name, email, phone number and address.
     *
     * @return LengthAwarePaginator<int, Supplier>
     */
    public function getAllSuppliers(SupplierFilterDTO $filter): LengthAwarePaginator;
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
