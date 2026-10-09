<?php

namespace App\Services;

use App\Repositories\Store\StoreInterface\StoreInterface;

class StoreService
{
    public function __construct(private readonly StoreInterface $store) {}
}
