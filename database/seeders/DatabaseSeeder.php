<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Project;
use App\Models\Task;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // إنشاء 5 مستخدمين بكلمة سر موحدة
        User::factory()->count(5)->create([
            'password' => Hash::make('password123'),
        ])->each(function ($user) {

            // لكل مستخدم 3 مشاريع عشوائية              
            $projects = Project::factory()->count(3)->create([
                'user_id' => $user->id,
            ]);

            // لكل مشروع 5 مهام
            $projects->each(function ($project) use ($user) {
                Task::factory()->count(5)->create([
                    'project_id' => $project->id,
                    'user_id'    => $user->id,
                ]);
            });
        });
    }
}
