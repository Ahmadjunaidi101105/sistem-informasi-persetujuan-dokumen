<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected static ?string $password = null;

    public function definition(): array
    {
        return [
            'name' => fake('id_ID')->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'phone' => fake('id_ID')->phoneNumber(),
            'company_name' => null,
            'company_address' => null,
            'is_active' => true,
            'remember_token' => Str::random(10),
        ];
    }

    public function pemohon(): static
    {
        return $this->state(fn () => [
            'company_name' => 'PT ' . fake('id_ID')->company(),
            'company_address' => fake('id_ID')->address(),
        ]);
    }

    public function penilai(): static
    {
        return $this->state(fn () => [
            'company_name' => null,
            'company_address' => null,
        ]);
    }

    public function unverified(): static
    {
        return $this->state(fn () => [
            'email_verified_at' => null,
        ]);
    }
}
