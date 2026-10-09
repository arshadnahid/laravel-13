<?php

namespace App\Repositories\Store;

use App\Repositories\Store\StoreInterface\StoreInterface;

class StoreRepository implements StoreInterface
{
    // Implementation for each method in the interface

    public function getAllStores(): array
    {
        // TODO
    }

    public function getStoreById(string $id): array
    {
        // TODO
    }

    public function createStore(array $data): array
    {
        // TODO
    }

    public function updateStore(string $id, array $data): array
    {
        // TODO
    }

    public function deleteStore(string $id): void
    {
        // TODO
    }

    public function slugExists(string $slug): bool
    {
        // TODO
    }
}
