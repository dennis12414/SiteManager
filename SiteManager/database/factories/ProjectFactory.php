<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'siteManagerId' => $this->faker->randomNumber(),
            'projectId' => $this->faker->randomNumber(), 
            'projectName' => $this->faker->word(),
            'projectDescription' => $this->faker->text(),
            'startDate' => $this->faker->date(),
            'endDate' => $this->faker->date(),  
        ];
    }
}
