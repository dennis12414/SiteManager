<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Controllers\Auth\AuthenticationController;
use Illuminate\Http\Request;
use App\Models\SiteManager;
use Illuminate\Support\Facades\Hash;
use Mockery;

class AuthorizationControllerTest extends TestCase
{
    use RefreshDatabase;
    public function test_register_creates_new_site_manager_and_sends_otp(): void
    {
        $this->withoutExceptionHandling();

        $request = new Request([
            'name' => 'Edwin',
            'email' => 'edwin@gmail.com',
            'phoneNumber' => '0723456789',
        ]);
        
        $controller = new AuthenticationController();
        $response = $controller->register($request);

        $this->assertEquals(201, $response->status());
      
    }

    public function test_verify_returns_site_manager_if_otp_is_valid()
    {
        $this->withoutExceptionHandling();

        
        $siteManager = SiteManager::factory()->create([
            'phoneNumber' => '1234567890',
            'otp' => '123456',
        ]);

        
        $request = new Request([
            'phoneNumber' => '1234567890',
            'otp' => '123456',
        ]);
        
        
        $controller = new AuthenticationController();
        $response = $controller->verify($request);

        
        $this->assertEquals(201, $response->status());

    }

     /**
     * A test set site manager password
     */
    public function test_set_site_manager_password()
    {
        $this->withoutExceptionHandling();

        $siteManager = SiteManager::factory()->create([
            'phoneNumber' => '0723456789',
            'password'=> null,
            'phoneVerified' => '1',
        ]);

        $request = new Request([
            'phoneNumber' => '0723456789',
            'password' => 'password',
        ]);

        $controller = new AuthenticationController();
        $response = $controller->setPassword($request);

        //assert that the response status is 201
        $this->assertEquals(201, $response->status());
        
    }

     /**
     * A test login when credentials are valid
     */
    public function test_login_if_credentials_are_valid()
    {
        $this->withoutExceptionHandling();

        $siteManager = SiteManager::factory()->create([
            'phoneNumber' => '0723456789',
            'password'=> Hash::make('password'),
            'phoneVerified' => '1',
        ]);

        $request = new Request([
            'phoneNumber' => '0723456789',
            'password' => 'password',
        ]);

        $controller = new AuthenticationController();
        $response = $controller->login($request);


        //assert that the response status is 201
        $this->assertEquals(201, $response->status());
        
    }

    /**
     * A test login when credentials are invalid
     */
    public function test_login_if_credentials_are_invalid()
    {
        $this->withoutExceptionHandling();

        $siteManager = SiteManager::factory()->create([
            'phoneNumber' => '0723456789',
            'password'=> Hash::make('password'),
            'phoneVerified' => '1',
        ]);

        $request = new Request([
            'phoneNumber' => '0723456789',
            'password' => 'password1',
        ]);

        $controller = new AuthenticationController();
        $response = $controller->login($request);

        $this->assertEquals(401, $response->status());
    }

}
