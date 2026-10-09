<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'store_name',
    'store_slug',
    'logo_url',
    'banner_url',
    'description'
])]
class Store extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected function casts(): array
    {
        return [
            'deleted_at' => 'datetime',
            'is_active' => 'boolean',
            'is_verified' => 'boolean',
        ];
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
