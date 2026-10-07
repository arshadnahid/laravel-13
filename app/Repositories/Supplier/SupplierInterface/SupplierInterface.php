<?php

namespace App\Repositories\Supplier\SupplierInterface;

use App\DTOs\Supplier\SupplierCreateDTO;
use App\DTOs\Supplier\SupplierFilterDTO;
use App\DTOs\Supplier\SupplierUpdateDTO;
use App\Models\Supplier;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface SupplierInterface
{
    /**
     * Paginated suppliers, optionally filtered by name, email, phone number and address.
     * Soft-deleted suppliers are excluded unless the filter's trashed option says otherwise.
     *
     * @return LengthAwarePaginator<int, Supplier>
     */
    public function getAllSuppliers(SupplierFilterDTO $filter): LengthAwarePaginator;
    /**
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getSupplierById(string $id): Supplier;

    /**
     * Insert a supplier from the request DTO plus the service-generated slug.
     */
    public function createSupplier(SupplierCreateDTO $dto, string $slug): Supplier;

    /**
     * Update the given fields of a supplier.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function updateSupplier(string $id, SupplierUpdateDTO $dto): Supplier;

    /**
     * Soft delete a supplier.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function deleteSupplier(string $id): void;

    /**
     * Whether the slug is taken, including by soft-deleted suppliers.
     */
    public function slugExists(string $slug): bool;
}
