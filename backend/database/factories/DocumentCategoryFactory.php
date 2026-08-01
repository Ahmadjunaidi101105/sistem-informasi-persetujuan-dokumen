<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentCategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(3, true),
            'code' => fake()->unique()->regexify('[A-Z]{3,8}'),
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
