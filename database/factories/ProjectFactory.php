<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(3), // اسم مشروع عشوائي
            'description' => $this->faker->paragraph(), // وصف عشوائي
        ];
    }
}
