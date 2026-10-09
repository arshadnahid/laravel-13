<?php

namespace Database\Factories;

use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Store>
 */
class StoreFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'store_name' => fake()->company(),
            'store_slug' => fake()->unique()->slug(),
            'logo_url' => fake()->imageUrl(200, 200, 'business', true),
            'banner_url' => fake()->imageUrl(800, 200, 'business', true),
            'description' => fake()->sentence(),
            'is_active' => fake()->boolean(),
            'is_verified' => fake()->boolean(),
        ];
    }
}
