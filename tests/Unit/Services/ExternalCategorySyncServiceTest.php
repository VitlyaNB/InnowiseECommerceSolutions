<?php

namespace Tests\Unit\Services;

use App\Models\Category;
use App\Services\ExternalCategorySyncService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExternalCategorySyncServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_syncs_categories_from_external_api()
    {
        Config::set('services.external_project.api_url', 'https://external-api.com/categories');

        Http::fake([
            'https://external-api.com/categories' => Http::response([
                ['name' => 'External Cat 1'],
                ['name' => 'External Cat 2'],
            ], 200)
        ]);

        $service = new ExternalCategorySyncService();

        $result = $service->sync();

        $this->assertTrue($result['ok']);
        $this->assertEquals(2, $result['synced']);
        $this->assertDatabaseHas('categories', ['name' => 'External Cat 1']);
        $this->assertDatabaseHas('categories', ['name' => 'External Cat 2']);
    }
}
