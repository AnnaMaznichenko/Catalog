<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Item;
use App\Models\Tag;
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

    public function test_items_show(): void
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

    public function test_items_create(): void
    {
        $category = Category::factory()-> create();
        $tag = Tag::factory()->create();
        $attributesItem = [
            "name" => "new item",
            "id_category" => $category->id,
        ];
        $attribytesTag = ["id_tags" => [$tag->id]];
        $response = $this->post("/api/items", array_merge($attributesItem, $attribytesTag));
        $response->assertStatus(201);
        $jsonArray = $response->json();
        $this->assertDatabaseHas("items", $attributesItem);
        $this->assertDatabaseHas("tag_item", [
            "id_item" => $jsonArray["id"],
            "id_tag" => $tag->id,
        ]);
    }

    public function test_items_create_validation(): void
    {
        $attributesItem = [
            "name" => "",
            "id_category" => -1,
        ];
        $attributesTag = ["id_tags" => [-1]];
        $response = $this->post("/api/items", array_merge($attributesItem, $attributesTag));
        $jsonArray = $response->json();
        $response->assertStatus(400);
        $this->assertDatabaseMissing("items", $attributesItem);
        $this->assertEquals("The name field is required.", $jsonArray["name"][0]);
        $this->assertEquals("The selected id category is invalid.", $jsonArray["id_category"][0]);
        $this->assertEquals("The selected id_tags.0 is invalid.", $jsonArray["id_tags.0"][0]);
    }

    public function test_items_update(): void
    {
        $item = Item::factory()->create();
        $category = Category::factory()-> create();
        $tag = Tag::factory()->create();
        $attributesItem = [
            "name" => "new new item",
            "id_category" => $category->id,
        ];
        $attributesTag = ["id_tags" => [$tag->id]];
        $response = $this->patch("/api/items/" . $item->id, array_merge(
            $attributesItem, $attributesTag
        ));
        $response->assertStatus(202);
        $this->assertDatabaseHas("items", array_merge(
            ["id" => $item->getKey()], $attributesItem
        ));
        $this->assertDatabaseHas("tag_item", [
            "id_item" => $item->id,
            "id_tag" => $tag->id,
        ]);
    }

    public function test_item_update_validation(): void
    {
        $item = Item::factory()->create();
        $attributesItem = [
            "name" => "",
            "id_category" => -1,
        ];
        $attributesTag = ["id_tags" => [-1]];
        $response = $this->patch("/api/items/" . $item->id, array_merge(
            $attributesItem, $attributesTag
        ));
        $jsonArray = $response->json();
        $response->assertStatus(400);
        $this->assertDatabaseMissing("items", array_merge(
            ["id" => $item->getKey()], $attributesItem
        ));
        $this->assertDatabaseMissing("tag_item", [
            "id_item" => $item->id,
            "id_tag" => $attributesTag["id_tags"]
        ]);
        $this->assertEquals("The name field is required.", $jsonArray["name"][0]);
        $this->assertEquals("The selected id category is invalid.", $jsonArray["id_category"][0]);
        $this->assertEquals("The selected id_tags.0 is invalid.", $jsonArray["id_tags.0"][0]);
    }

    public function test_item_delete(): void
    {
        $item = Item::factory()->create();
        $response = $this->delete("/api/items/" . $item->getKey());
        $response->assertStatus(204);
        $this->assertDatabaseMissing("items", ["id" => $item->getKey()]);
    }
}
