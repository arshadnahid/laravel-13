<?php

namespace App\DTOs\Store;

class StoreCreateDTO
{
    public function __construct(
        public readonly string $user_id,
        public readonly string $store_name,
        public readonly ?string $logo_url = null,
        public readonly ?string $banner_url = null,
        public readonly ?string $description = null,
        public readonly ?bool $is_active = true,
        public readonly ?bool $is_verified = false,
    ) {}

    public function toArray(): array
    {
        return [
            'user_id' => $this->user_id,
            'store_name' => $this->store_name,
            'logo_url' => $this->logo_url,
            'banner_url' => $this->banner_url,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'is_verified' => $this->is_verified,
        ];
    }
}
