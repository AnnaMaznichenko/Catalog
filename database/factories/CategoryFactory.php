<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            "name" => $this->faker->unique()->sentence(1),
            "id_category" => function() {
                $categories = Category::pluck("id")->toArray();
                return $this->faker->randomElement($categories);
            }
        ];
    }
}
