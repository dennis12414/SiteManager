<?php

namespace Tests\Unit;


use App\Http\Controllers\SiteManager\SiteManagerController;

use App\Models\SiteManager;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class SiteManagerControllerTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    use RefreshDatabase;
    public function test_index_returns_list_of_site_managers(): void
    {
        $this->withoutExceptionHandling();
        $siteManager = SiteManager::factory()->create();
        $controller = new SiteManagerController();
        $response = $controller->index();
        $this->assertEquals(200, $response->status());
    }
}
