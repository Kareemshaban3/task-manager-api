<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(4), // عنوان المهمة
            'description' => $this->faker->paragraph(), // وصف المهمة
            'mode' => $this->faker->randomElement(['pending', 'in_progress', 'completed']),
        ];
    }
}
