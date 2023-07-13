<?php

namespace Tests\Unit;

use App\Http\Controllers\Project\ProjectController;
use App\Models\Project;
use App\Models\SiteManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Tests\TestCase;

class ProjectControllerTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    public function test_store_creates_new_project_record(): void
    {
        $this->withoutExceptionHandling();

        // Create a sitemanager
        $siteManager = SiteManager::factory()->create();
        $request = new Request([
            'siteManagerId' => $siteManager->siteManagerId,
            'projectName' => 'Project 1',
            'projectDescription' => 'project 1 description',
            'startDate' => '2023-07-13',
            'endDate' => '2023-07-20',
        ]);

        // Call the store method 
        $controller = new ProjectController();
        $response = $controller->store($request);

        // Assert that the response status is 201
        $this->assertEquals(201, $response->status());

        // Assert that new project was created
        // $this->assertDatabaseHas('projects', [
        //     'siteManagerId' => $siteManager->siteManagerId,
        //     'projectName' => 'Project 1',
        //     'projectDescription' => 'project 1 description',
        //     'startDate' => '2023-07-13',
        //     'endDate' => '2023-07-20',
        // ]);

    }
    public function test_store_returns_error_if_site_manager_does_not_exist()
    {
        $this->withoutExceptionHandling();

        $request = new Request([
            'siteManagerId' => 1,
            'projectName' => 'Project 1',
            'projectDescription' => 'project description',
            'startDate' => '2023-07-13',
            'endDate' => '2023-07-20',
        ]);

        // Call the store method on the ProjectController
        $controller = new ProjectController();
        $response = $controller->store($request);

        // Assert that the response status is 404
        $this->assertEquals(404, $response->status());
    }
    public function test_show_returns_projects_for_site_manager()
    {
        $this->withoutExceptionHandling();

        $siteManager = SiteManager::factory()->create();
        $project1 = Project::factory()->create([
            'siteManagerId' => $siteManager->siteManagerId,
        ]);

        $project2 = Project::factory()->create([
            'siteManagerId' => $siteManager->siteManagerId,
        ]);

        // Call the show method on the ProjectController
        $controller = new ProjectController();
        $response = $controller->show($siteManager->siteManagerId);

        // Assert that the response status is 200
        $this->assertEquals(200, $response->status());
    }
    public function test_show_returns_error_if_no_projects_exist_for_site_manager()
    {
        $this->withoutExceptionHandling();

        $siteManager = SiteManager::factory()->create();

        $controller = new ProjectController();
        $response = $controller->show($siteManager->siteManagerId);

        $this->assertEquals(404, $response->status());

    }
    public function test_details_returns_project_with_given_id()
    {
        // $this->withoutExceptionHandling();

        // $siteManager = SiteManager::factory()->create();
        // $project = Project::factory()->create([
        //     'siteManagerId' => $siteManager->siteManagerId,
        // ]);

        // $controller = new ProjectController();
        // $response = $controller->details($project->projectId);

        // $this->assertEquals(200, $response->status());

    }

    public function test_details_returns_error_if_project_does_not_exist()
    {
        $this->withoutExceptionHandling();

        $controller = new ProjectController();
        $response = $controller->details(100);

        $this->assertEquals(404, $response->status());
    }

    public function test_update_updates_existing_project_record()
    {
        $this->withoutExceptionHandling();

        $siteManager = SiteManager::factory()->create();
        $project = Project::factory()->create([
            'siteManagerId' => $siteManager->siteManagerId,
        ]);

        $request = new Request([
            'siteManagerId' => $siteManager->siteManagerId,
            'projectName' => 'Updated Name',
            'projectDescription' => 'Updated Description',
            'startDate' => '2023-07-12',
            'endDate' => '2023-07-20',
        ]);

        $controller = new ProjectController();
        $response = $controller->update($request, $project->projectId);

        $this->assertEquals(201, $response->status());

    
    }
    public function test_update_returns_error_if_site_manager_does_not_exist()
    {
        // $this->withoutExceptionHandling();

        // $project = Project::factory()->create();

        // $request = new Request([
        //     'siteManagerId' => 1,
        //     'projectName' => 'Updated Name',
        //     'projectDescription' => 'Updated Description',
        //     'startDate' => '2023-07-12',
        //     'endDate' => '2023-07-20',
        // ]);

        // $controller = new ProjectController();
        // $response = $controller->update($request, $project->projectId);

        // $this->assertEquals(404, $response->status());

    }
    public function test_archive_project()
    {
        // $this->withoutExceptionHandling();
        // $siteManager = SiteManager::factory()->create();
        // $project = Project::factory()->create();

        // $controller = new ProjectController();
        // $response = $controller->archive($project->projectId, $siteManager->siteManagerId);

        // $this->assertEquals(200, $response->status());
    }

}
