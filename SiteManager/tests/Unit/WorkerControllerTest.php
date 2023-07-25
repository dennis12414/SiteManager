<?php

namespace Tests\Unit;


use Illuminate\Foundation\Testing\WithFaker;
use App\Http\Controllers\Worker\WorkerController;
use App\Models\SiteManager;
use App\Models\Worker;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class WorkerControllerTest extends TestCase
{
    use RefreshDatabase; 
    public function test_store_create_new_worker(): void
    {
            //$this->withoutExceptionHandling(); 
            $siteManager = SiteManager::factory()->create(); 
            
            $worker = new Request([
                'name' => 'Edwin',
                'phoneNumber' => '07234567899',
                'dateRegistered' => '2023-03-08',
                'payRate' => 1000,
                'siteManagerId' => $siteManager->siteManagerId, 
            ]);

            $controller = new WorkerController(); 
            $response = $controller->store($worker); // we

            //assert that the worker was created
            $this->assertEquals(201, $response->status());

            //assert json response
            $this->assertJson($response->content([
                'name' => 'Edwin',
                'phoneNumber' => '07234567899',
                'dateRegistered' => '2023-03-08',
                'payRate' => 1000,
                'siteManagerId' => $siteManager->siteManagerId,
            ]));


            //assert that the worker was created in the database
            $this->assertDatabaseHas('workers', [
                'name' => 'Edwin',
                'phoneNumber' => '07234567899',
                'dateRegistered' => '2023-03-08',
                'payRate' => 1000,
                'siteManagerId' => $siteManager->siteManagerId,
            ]); 

    }

    /**
     * test searching a worker
     */
    public function test_can_search_worker(): void
    {
        //$this->withoutExceptionHandling(); 
        $siteManager = SiteManager::factory()->create();
        $worker = Worker::factory()->create([
            'name' => 'John Munene',
            'phoneNumber' => '07012345678',
            'dateRegistered' => '2023-07-01',
            'payRate' => 1000,
            'siteManagerId' => $siteManager->siteManagerId,
        ]);

        $controller = new WorkerController();
        $response = $controller->search($worker->siteManagerId, '07012345678');

        $this->assertEquals(200, $response->status());     
        
    }

    /**
     * test search return error if worker not found
     */
    public function test_search_returns_not_found_if_worker_not_found(): void
    {
        $this->withoutExceptionHandling();
        $siteManager = SiteManager::factory()->create();
        $worker = Worker::factory()->create([
            'name' => 'John Munene',
            'phoneNumber' => '07012345678',
            'dateRegistered' => '2023-07-01',
            'payRate' => 1000,
            'siteManagerId' => $siteManager->siteManagerId,
        ]);

        $controller = new WorkerController();
        $response = $controller->search(1, 'fsgdfg');
        //dd($response);
        $this->assertEquals(404, $response->status());     
        
    }

    /**
     * test if we can update a worker
     */
    public function test_update_updates_worker(): void
    {
        $this->withoutExceptionHandling();

        $siteManager = SiteManager::factory()->create();
        $worker = Worker::factory()->create([
            'name' => 'John Munene',
            'phoneNumber' => '07012345678',
            'dateRegistered' => '2023-07-01',
            'payRate' => 1000,
            'siteManagerId' => $siteManager->siteManagerId,
        ]);

        $updatedworker = new Request([
            'name' => 'Ben Munene',
            'phoneNumber' => '07034345678',
            'dateRegistered' => '2023-07-01',
            'payRate' => 1000,
            'siteManagerId' => $siteManager->siteManagerId,
        ]);

        $controller = new WorkerController();
        $response = $controller->update($updatedworker, $worker->siteManagerId, $worker->phoneNumber);

        $this->assertEquals(200, $response->status());       
    }

    /**
     * test if we can delete a worker
     */
    public function test_delete_deletes_worker(): void
    {
        //$this->withoutExceptionHandling();

        $siteManager = SiteManager::factory()->create();
        $worker = Worker::factory()->create([
            'name' => 'John Munene',
            'phoneNumber' => '07012345678',
            'dateRegistered' => '2023-07-01',
            'payRate' => 1000,
            'siteManagerId' => $siteManager->siteManagerId,
        ]);

        $controller = new WorkerController();
        $response = $controller->archive($worker->phoneNumber, $worker->siteManagerId);

        $this->assertEquals(200, $response->status());       
    }
  
}
