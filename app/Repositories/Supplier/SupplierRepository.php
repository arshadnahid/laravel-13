<?php

namespace App\Repositories\Supplier;

use App\DTOs\Supplier\SupplierCreateDTO;
use App\DTOs\Supplier\SupplierFilterDTO;
use App\DTOs\Supplier\SupplierUpdateDTO;
use App\Models\Supplier;
use App\Repositories\Supplier\SupplierInterface\SupplierInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SupplierRepository implements SupplierInterface
{
    public function getAllSuppliers(SupplierFilterDTO $filter): LengthAwarePaginator
    {
        return Supplier::query()
            ->when($filter->name, fn ($q, $v) => $q->where('name', 'like', $this->like($v)))
            ->when($filter->email, fn ($q, $v) => $q->where('email', 'like', $this->like($v)))
            ->when($filter->phoneNumber, fn ($q, $v) => $q->where('phone_number', 'like', $this->like($v)))
            ->when($filter->address, fn ($q, $v) => $q->where('address', 'like', $this->like($v)))
            ->when($filter->trashed === 'only', fn ($q) => $q->onlyTrashed())
            ->when($filter->trashed === 'with', fn ($q) => $q->withTrashed())
            ->latest()
            ->paginate($filter->perPage)
            ->withQueryString();
    }

    public function getSupplierById(string $id): Supplier
    {
        return Supplier::findOrFail($id);
    }

    public function createSupplier(SupplierCreateDTO $dto, string $slug): Supplier
    {
        return Supplier::create([...$dto->toArray(), 'slug' => $slug]);
    }

    public function updateSupplier(string $id, SupplierUpdateDTO $dto): Supplier
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->update($dto->toArray());

        return $supplier;
    }

    public function deleteSupplier(string $id): void
    {
        Supplier::findOrFail($id)->delete();
    }

    public function slugExists(string $slug): bool
    {
        return Supplier::withTrashed()->where('slug', $slug)->exists();
    }

    /**
     * Contains-match pattern with LIKE wildcards in the input escaped.
     */
    private function like(string $value): string
    {
        return '%'.addcslashes($value, '\%_').'%';
    }
}
