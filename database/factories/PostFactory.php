<?php

namespace Database\Factories;

use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        $title = fake()->sentence(4);

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::random(5),
            'excerpt' => fake()->sentence(12),
            'content' => fake()->paragraphs(4, true),
            'cover_path' => null,
            'is_published' => true,
            'published_at' => now()->subDays(fake()->numberBetween(0, 30)),
        ];
    }
}
