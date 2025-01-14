<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\ProjectStatus;
use App\Models\User;
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
    protected $model = Project::class;

    public function definition()
    {
        return [
            'name' => $this->faker->company, // اسم المشروع
            'description' => $this->faker->paragraph, // وصف المشروع
            'status_id' => ProjectStatus::inRandomOrder()->first()->id, // حالة المشروع
            'owner_id' => User::inRandomOrder()->first()->id, // مالك المشروع
            'ticket_prefix' => $this->faker->word, // بادئة التذكرة
            'status_type' => $this->faker->word, // نوع الحالة (مثلاً: نشط أو معلق)
            'type' => $this->faker->word, //
        ];
    }
}
