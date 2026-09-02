<?php

namespace Database\Seeders;

use App\Models\Partner;
use App\Models\Plot;
use App\Models\Post;
use App\Models\Program;
use App\Models\Project;
use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            return;
        }

        $programs = Program::factory()->count(3)->create();

        foreach ($programs as $program) {
            Plot::factory()->count(8)->for($program)->create();
            // Ensure at least 2 disponibles per program
            Plot::factory()->count(2)->for($program)->available()->create();
        }

        Project::factory()->count(6)->create();
        Project::factory()->count(3)->featured()->create();

        Testimonial::factory()->count(3)->create();
        Partner::factory()->count(5)->create();
        Post::factory()->count(3)->create();
    }
}
