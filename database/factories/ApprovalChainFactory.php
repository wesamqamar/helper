<?php
namespace Database\Factories;

use App\Models\ApprovalChain;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class ApprovalChainFactory extends Factory
{
    protected $model = ApprovalChain::class;

    public function definition()
    {
        return [
            'project_id' => Project::inRandomOrder()->first()->id, // ربط المشروع عشوائيًا
        ];
    }
}
