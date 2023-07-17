<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SiteManager>
 */
class SiteManagerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phoneNumber' => $this->faker->phoneNumber(),
            'otp' => $this->faker->randomNumber(6),
            'phoneVerified' => $this->faker->boolean(),
        ];
    }
}
