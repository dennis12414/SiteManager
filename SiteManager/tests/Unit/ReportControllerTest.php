<?php

namespace Tests\Unit;

use App\Http\Controllers\Report\ReportController;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\ClockIns;
use App\Models\Project;
use App\Models\Worker;
use App\Models\SiteManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class ReportControllerTest extends TestCase
{

    use RefreshDatabase; 
    public function test_generateReport_returns_report_data_for_valid_project(): void
    {
        $this->withoutExceptionHandling();
        //create a new project, two workers, and three clockins
        $siteManager = SiteManager::factory()->create();
        $project = Project::factory()->create([
            'siteManagerId'=> $siteManager->siteManagerId
        ]);
        $worker1 = Worker::factory()->create([
            'payRate'=> 1000,
            'siteManagerId'=> $siteManager->siteManagerId,
        ]);
        $worker2 = Worker::factory()->create([
            'payRate'=> 2000,
            'siteManagerId'=> $siteManager->siteManagerId,
        ]);
        ClockIns::factory()->create([
            'workerId' => $worker1->workerId,
            'projectId' => $project->projectId,
            'siteManagerId'=> $siteManager->siteManagerId,
            'clockInTime' => '2021-07-13 08:00:00',
        ]);
        ClockIns::factory()->create([
            'workerId' => $worker1->workerId,
            'projectId' => $project->projectId,
            'siteManagerId'=> $siteManager->siteManagerId,
            'clockInTime' => '2021-07-14 08:00:00',
        ]);
        ClockIns::factory()->create([
            'workerId' => $worker2->workerId,
            'projectId' => $project->projectId,
            'siteManagerId'=> $siteManager->siteManagerId,
            'clockInTime' => '2021-07-13 08:00:00',
        ]);

        //call the generateReport method 
        $controller = new ReportController();
        $response = $controller->generateReport($project->projectId);

   

        //assert that the response is a stream
        $this->assertEquals(200, $response->status());
        
       
    }
}
