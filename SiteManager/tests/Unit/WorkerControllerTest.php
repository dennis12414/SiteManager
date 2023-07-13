<?php

namespace Tests\Unit;


use Illuminate\Foundation\Testing\WithFaker;
use App\Models\SiteManager;
use App\Models\Worker;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkerControllerTest extends TestCase
{
    use RefreshDatabase; 
    public function test_create_new_worker(): void
    {
         $siteManager = SiteManager::factory()->create(); 
            $data = [
                'name' => 'Edwin',
                'phoneNumber' => '07234567899',
                'dateRegistered' => '2023-03-08',
                'payRate' => 1000,
                'siteManagerId' => $siteManager->siteManagerId,
            ];
            $response = $this->postJson(route('workers.store'), $data);

            $response->assertStatus(201);

            $response->assertJson([
                'message' => 'Worker created successfully',
                'worker' => [
                    'name' => 'Edwin',
                    'phoneNumber' => '07234567899',
                    'dateRegistered' => '2023-03-08',
                    'payRate' => 1000,
                    'siteManagerId' => $siteManager->siteManagerId,
                ],
            ]); //assertJson method asserts that the given response contains the exact JSON data passed to the method.

            $this->assertDatabaseHas('workers', [
                'name' => 'Edwin',
                'phoneNumber' => '07234567899',
                'dateRegistered' => '2023-03-08',
                'payRate' => 1000,
                'siteManagerId' => $siteManager->siteManagerId,
            ]); //assertDatabaseHas method asserts that data exists in the database that matches a given set of criteria.

    }

    public function test_can_search_worker(): void
    {
        $response = $this->call('GET', '/api/workers/search', [
            'phoneNumber' => '07012345678',
        ]);
        $this->assertEquals(200, $response->status());

        // $siteManager = SiteManager::factory()->create();
        // $worker = Worker::factory()->create([
        //     'name' => 'John Doe',
        //     'phoneNumber' => '08012345678',
        //     'dateRegistered' => now()->toDateString(),
        //     'payRate' => 1000,
        //     'siteManagerId' => 1,
        // ]);

        // $response = $this->getJson(route('workers.search', ['name' => 'John Doe']));
        // $response->assertStatus(200);
        // $response->assertJson([
        //     'message' => 'Worker found',
        //     'worker' => [
        //         'name' => 'John Doe',
        //         'phoneNumber' => '08012345678',
        //         'dateRegistered' => now()->toDateString(),
        //         'payRate' => 1000,
        //         'siteManagerId' => 1,
        //     ],
        // ]);

        
        
    }

    public function itCanUpdateAWorker(): void
    {
       
    }

    public function itCanDeleteAWorker(): void
    {
        
    }
}
