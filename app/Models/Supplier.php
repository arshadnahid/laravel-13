<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'name',
    'slug',
    'email',
    'phone_number',
    'description',
    'address',
    'website',
    'logo_url'
])]
class Supplier extends Model
{
    use HasFactory, HasUlids, SoftDeletes;
    protected function casts(): array
    {
        return [
            'deleted_at' => 'datetime',
        ];
    }
}
