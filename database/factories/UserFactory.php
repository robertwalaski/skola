<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nick' => fake()->unique()->userName(),
            'pin_hash' => '1234', // hashed automatically by User's `pin_hash => hashed` cast
            'email' => null,
            'password' => null,
            'role' => 'student',
            'learner_locale' => 'pl',
            'is_adult' => false,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the user is an adult with email/password login.
     */
    public function adult(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_adult' => true,
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => 'password', // hashed automatically by User's `password => hashed` cast
        ]);
    }
}
