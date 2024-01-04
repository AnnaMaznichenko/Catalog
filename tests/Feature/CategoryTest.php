<?php

namespace Tests\Feature;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_categories_index(): void
    {
        Category::factory(15)->create();
        $response = $this->get('/api/categories');
        $response->assertStatus(200);
        $jsonArray = $response->json();
        $this->assertArrayHasKey("name", $jsonArray["data"][0]);
        $this->assertArrayHasKey("id_category", $jsonArray["data"][0]);
        $this->assertEquals(15, $jsonArray["total"]);
        $this->assertEquals(1, $jsonArray["current_page"]);
        $this->assertCount(10, $jsonArray["data"]);
    }

    public function test_categories_show(): void
    {
        $category = Category::factory()->create();
        $response = $this->get('/api/categories/' . $category->getKey());
        $response->assertStatus(200);
        $jsonArray = $response->json();
        $this->assertEquals($category->id, $jsonArray["id"]);
        $this->assertEquals($category->name, $jsonArray["name"]);
        $this->assertEquals($category->id_category, $jsonArray["id_category"]);
    }

    public function test_category_create():void
    {
        $category = Category::factory()->create();
        $attributes = [
            "name" => "new category",
            "id_category" => $category->id,
        ];
        $response = $this->post('/api/categories', $attributes);
        $response->assertStatus(201);
        $this->assertDatabaseHas("categories", $attributes);
    }

    public function test_category_create_validation(): void
    {
        $attributes = [
            "name" => "",
            "id_category" => -1,
        ];
        $response = $this->post("/api/categories", $attributes);
        $jsonArray = $response->json();
        $response->assertStatus(400);
        $this->assertDatabaseMissing("categories", $attributes);
        $this->assertEquals("The name field is required.", $jsonArray["name"][0]);
        $this->assertEquals("The selected id category is invalid.", $jsonArray["id_category"][0]);
    }

    public function test_category_update(): void
    {
        $category = Category::factory()->create();
        $parentCategory = Category::factory()->create();
        $attributes = [
            "name" => "new new category",
            "id_category" => $parentCategory->id,
        ];
        $response = $this->patch("/api/categories/" . $category->getKey(), $attributes);
        $response->assertStatus(202);
        $this->assertDatabaseHas("categories", array_merge(
            ["id" => $category->getKey()], $attributes
        ));
    }

    public function test_category_update_validation(): void
    {
        $category = Category::factory()->create();
        $attributes = [
            "name" => "",
            "id_category" => $category->id,// Категория ссылается сама на себя
        ];
        $response = $this->patch("/api/categories/" . $category->getKey(), $attributes);
        $response->assertStatus(400);
        $jsonArray = $response->json();
        $this->assertDatabaseMissing("categories", array_merge(
            ["id" => $category->getKey()], $attributes
        ));
        $this->assertEquals("The name field is required.", $jsonArray["name"][0]);
        $this->assertEquals(
            "The id category field and id must be different.",
            $jsonArray["id_category"][0]
        );
    }

    public function test_category_delete(): void
    {
        $category = Category::factory()->create();
        $response = $this->delete("/api/categories/" . $category->getKey());
        $response->assertStatus(204);
        $this->assertDatabaseMissing("categories", ["id" => $category->getKey()]);
    }
}
