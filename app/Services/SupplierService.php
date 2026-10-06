<?php

namespace App\Services;

use App\Repositories\Supplier\SupplierInterface\SupplierInterface;

class SupplierService
{
    public function __construct(private readonly SupplierInterface $supplierRepository) {}
}
