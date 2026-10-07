<?php

namespace App\Rules;

use App\Models\Supplier;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Fails when the value belongs to a soft-deleted supplier, with a message that says
 * so, instead of the generic "already taken" error.
 */
class NotUsedBySoftDeletedSupplier implements ValidationRule
{
    public function __construct(
        private readonly string $column,
        private readonly ?string $ignoreId = null,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $used = Supplier::onlyTrashed()
            ->where($this->column, $value)
            ->when($this->ignoreId, fn ($q, $id) => $q->whereKeyNot($id))
            ->exists();

        if ($used) {
            $fail('The :attribute is already used by a supplier that is currently inactive (soft deleted).');
        }
    }
}
