<?php

namespace App\Repositories\Supplier;

use App\DTOs\Supplier\SupplierCreateDTO;
use App\Models\Supplier;
use App\Repositories\Supplier\SupplierInterface\SupplierInterface;

class SupplierRepository implements SupplierInterface
{
    public function getAllSuppliers()
    {
        // TODO
    }

    public function getSupplierById(string $id)
    {
        // TODO
    }

    public function createSupplier(SupplierCreateDTO $dto, string $slug): Supplier
    {
        return Supplier::create([...$dto->toArray(), 'slug' => $slug]);
    }

    public function updateSupplier(string $id, array $data)
    {
        // TODO
    }

    public function deleteSupplier(string $id)
    {
        // TODO
    }

    public function slugExists(string $slug): bool
    {
        return Supplier::withTrashed()->where('slug', $slug)->exists();
    }
}
