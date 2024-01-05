<?php

namespace Tests\Feature;

use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TagTest extends TestCase
{
    use RefreshDatabase;

    public function test_tags_index(): void
    {
        Tag::factory(10)->create();
        $response = $this->get('/api/tags');
        $response->assertStatus(200);
        $jsonArray = $response->json();
        $this->assertArrayHasKey("name", $jsonArray["data"][0]);
        $this->assertEquals(10, $jsonArray["total"]);
        $this->assertEquals(1, $jsonArray["current_page"]);
        $this->assertCount(10, $jsonArray["data"]);
    }

    public function test_tags_show(): void
    {
        $tag = Tag::factory()->create();
        $response = $this->get('/api/tags/' . $tag->getKey());
        $response->assertStatus(200);
        $jsonArray = $response->json();
        $this->assertEquals($tag->id, $jsonArray["id"]);
        $this->assertEquals($tag->name, $jsonArray["name"]);
    }

    public function test_tags_create(): void
    {
        $attributes = [
            "name" => "new tag",
        ];
        $response = $this->post("/api/tags", $attributes);
        $response->assertStatus(201);
        $this->assertDatabaseHas("tags", $attributes);
    }

    public function test_tags_create_validation(): void
    {
        $attributes = [
            "name" => "",
        ];
        $response = $this->post("/api/tags", $attributes);
        $jsonArray = $response->json();
        $response->assertStatus(400);
        $this->assertDatabaseMissing("tags", $attributes);
        $this->assertEquals("The name field is required.", $jsonArray["name"][0]);
    }
    public function test_tags_update(): void
    {
        $tag = Tag::factory()->create();
        $attributes = [
            "name" => "new new tag",
        ];
        $response = $this->patch("/api/tags/" . $tag->getKey(), $attributes);
        $response->assertStatus(202);
        $this->assertDatabaseHas("tags", array_merge(
            ["id" => $tag->getKey()], $attributes
        ));
    }

    public function test_tags_update_validation(): void
    {
        $tag = Tag::factory()->create();
        $attributes = [
            "name" => "",
        ];
        $response = $this->patch("/api/tags/" . $tag->getKey(), $attributes);
        $jsonArray = $response->json();
        $response->assertStatus(400);
        $this->assertDatabaseMissing("tags", $attributes);
        $this->assertEquals("The name field is required.", $jsonArray["name"][0]);
    }
    public function test_tags_delete(): void
    {
        $tag = Tag::factory()->create();
        $response = $this->delete("/api/tags/" . $tag->getKey());
        $response->assertStatus(204);
        $this->assertDatabaseMissing("tags", ["id" => $tag->getKey()]);
    }
}
