<?php

namespace App\DTOs\Supplier;

class SupplierUpdateDTO
{
    /**
     * @param  array<string, mixed>  $attributes  Only the columns present in the request,
     *                                            so an omitted field is left untouched while an explicit null clears it.
     */
    public function __construct(private readonly array $attributes) {}

    /**
     * Column => value for the fields to change. The slug is never updated, so URLs stay stable.
     */
    public function toArray(): array
    {
        return $this->attributes;
    }
}
