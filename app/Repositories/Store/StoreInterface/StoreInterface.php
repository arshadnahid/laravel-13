<?php

namespace App\Repositories\Store\StoreInterface;

interface StoreInterface
{


    public function getAllStores(): array;
    public function getStoreById(string $id): array;
    public function createStore(array $data): array;
    public function updateStore(string $id, array $data): array;
    public function deleteStore(string $id): void;
    public function slugExists(string $slug): bool;
}
