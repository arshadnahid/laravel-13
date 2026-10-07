<?php

namespace App\Services;

use App\DTOs\Supplier\SupplierCreateDTO;
use App\DTOs\Supplier\SupplierFilterDTO;
use App\Models\Supplier;
use App\Repositories\Supplier\SupplierInterface\SupplierInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class SupplierService
{
    public function __construct(private readonly SupplierInterface $supplierRepository) {}

    public function getAllSuppliers(SupplierFilterDTO $filter): LengthAwarePaginator
    {
        return $this->supplierRepository->getAllSuppliers($filter);
    }

    public function createSupplier(SupplierCreateDTO $dto): Supplier
    {
        return $this->supplierRepository->createSupplier($dto, $this->uniqueSlug($dto->name));
    }

    /**
     * "Acme Ltd" -> "acme-ltd", then "acme-ltd-2", "acme-ltd-3"... when taken.
     */
    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: Str::lower((string) Str::ulid());
        $slug = $base;

        for ($i = 2; $this->supplierRepository->slugExists($slug); $i++) {
            $slug = "{$base}-{$i}";
        }

        return $slug;
    }
}
