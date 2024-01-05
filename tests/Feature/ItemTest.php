<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ItemTest extends TestCase
{
    use RefreshDatabase;

    public function test_items_index(): void
    {
        Item::factory(15)->create();
        $response = $this->get("/api/items");
        $response->assertStatus(200);
        $jsonArray = $response->json();
        $this->assertArrayHasKey("name", $jsonArray["data"][0]);
        $this->assertArrayHasKey("id_category", $jsonArray["data"][0]);
        $this->assertArrayHasKey("tags", $jsonArray["data"][0]);
        $this->assertEquals(15, $jsonArray["total"]);
        $this->assertEquals(1, $jsonArray["current_page"]);
        $this->assertCount(10, $jsonArray["data"]);
    }

    public function test_items_show()
    {
        $item = Item::factory()->create();
        $response = $this->get("/api/items/" . $item->getKey());
        $response->assertStatus(200);
        $jsonArray = $response->json();
        $this->assertEquals($item->id, $jsonArray["id"]);
        $this->assertEquals($item->name, $jsonArray["name"]);
        $this->assertEquals($item->id_category, $jsonArray["id_category"]);
        $this->assertArrayHasKey("tags", $jsonArray);
    }

    public function test_items_create()
    {
        $category = Category::factory()-> create();
        $attributes = [
            "name" => "new item",
            "id_category" => $category->id,
        ];
        $response = $this->post("/api/items", $attributes);
        $response->assertStatus(201);
        $this->assertDatabaseHas("items", $attributes);
    }

    public function test_items_create_validation()
    {
        $attributes = [
            "name" => "",
            "id_category" => -1,
        ];
        $response = $this->post("/api/items", $attributes);
        $jsonArray = $response->json();
        $response->assertStatus(400);
        $this->assertDatabaseMissing("items", $attributes);
        $this->assertEquals("The name field is required.", $jsonArray["name"][0]);
        $this->assertEquals("The selected id category is invalid.", $jsonArray["id_category"][0]);
    }

    public function test_items_update()
    {
        $item = Item::factory()->create();
        $category = Category::factory()-> create();
        $attributes = [
            "name" => "new new item",
            "id_category" => $category->id,
        ];
        $response = $this->patch("/api/items/" . $item->getKey(), $attributes);
        $response->assertStatus(202);
        $this->assertDatabaseHas("items", array_merge(
            ["id" => $item->getKey()], $attributes
        ));
    }

    public function test_item_update_validation()
    {
        $item = Item::factory()->create();
        $attributes = [
            "name" => "",
            "id_category" => -1,
        ];
        $response = $this->patch("/api/items/" . $item->getKey(), $attributes);
        $jsonArray = $response->json();
        $response->assertStatus(400);
        $this->assertDatabaseMissing("items", array_merge(
            ["id" => $item->getKey()], $attributes
        ));
        $this->assertEquals("The name field is required.", $jsonArray["name"][0]);
        $this->assertEquals(
            "The selected id category is invalid.",
            $jsonArray["id_category"][0]
        );
    }

    public function test_item_delete()
    {
        $item = Item::factory()->create();
        $response = $this->delete("/api/items/" . $item->getKey());
        $response->assertStatus(204);
        $this->assertDatabaseMissing("items", ["id" => $item->getKey()]);
    }
}
