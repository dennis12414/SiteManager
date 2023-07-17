<?php

namespace Tests\Unit;
use App\Http\Controllers\ClockIns\ClockInsController;
use App\Models\ClockIns;
use Tests\TestCase;
use App\Models\Project;
use App\Models\SiteManager;
use App\Models\Worker;
use Illuminate\Http\Request;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class ClockInsControllerTest extends TestCase
{
    use RefreshDatabase;
    public function test_clockIn_creates_ne_clockin_record(): void
    {
        $this->withoutExceptionHandling();
        
        $siteManager = SiteManager::factory()->create();
        $project = Project::factory()->create([
            'siteManagerId' => $siteManager->siteManagerId,
        ]);
        $worker = Worker::factory()->create([
            'siteManagerId' => $siteManager->siteManagerId,
        ]);
        $request = new Request([
            'siteManagerId' => $siteManager->siteManagerId,
            'projectId' => $project->projectId,
            'workerId' => $worker->workerId,
            'clockInTime' => '2021-08-01',
        ]);

        
        $controller = new ClockInsController();
        $response = $controller->clockIn($request);

        
        $this->assertEquals(201, $response->status());

    }

    public function test_clock_in_returns_error_if_worker_already_clocked_in()
    {
        $this->withoutExceptionHandling();
        $siteManager = SiteManager::factory()->create();
        $project = Project::factory()->create([
            'siteManagerId' => $siteManager->siteManagerId,
        ]);
        $worker = Worker::factory()->create([
            'siteManagerId' => $siteManager->siteManagerId,
        ]);

        //create a clock in record in the database
        $clockIn = ClockIns::factory()->create([
            'siteManagerId' => $siteManager->siteManagerId,
            'projectId' => $project->projectId,
            'workerId' => $worker->workerId,
            'clockInTime' => '2021-08-01',
        ]);

        $request = new Request([
            'siteManagerId' => $siteManager->siteManagerId,
            'projectId' => $project->projectId,
            'workerId' => $worker->workerId,
            'clockInTime' => '2021-08-01',
        ]);
        $controller = new ClockInsController();
        $response = $controller->clockIn($request);
        //call the clockIn method
        $controller = new ClockInsController();
        $response = $controller->clockIn($request);

        $this->assertEquals(409, $response->status());
       
    }
}
