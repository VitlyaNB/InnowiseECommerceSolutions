<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_anyone_can_list_categories(): void
    {
        Category::factory()->count(3)->create();

        $response = $this->getJson('/api/categories');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    public function test_non_admin_cannot_create_category(): void
    {
        $user = User::factory()->createOne(['role' => 'user']);

        $response = $this->actingAs($user)
            ->postJson('/api/categories', [
                'name' => 'New Category',
            ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_create_category(): void
    {
        $admin = User::factory()->createOne(['role' => 'admin']);

        $response = $this->actingAs($admin)
            ->postJson('/api/categories', [
                'name' => 'Admin Category',
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('categories', ['name' => 'Admin Category']);
    }
}
