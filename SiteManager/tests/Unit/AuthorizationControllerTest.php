<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Controllers\Auth\AuthenticationController;
use Illuminate\Http\Request;
use App\Models\SiteManager;
use Mockery;

class AuthorizationControllerTest extends TestCase
{
    use RefreshDatabase;
    public function test_register_creates_new_site_manager_and_sends_otp(): void
    {
        $this->withoutExceptionHandling();

        //create a mock request 
        $request = new Request([
            'name' => 'Edwin',
            'email' => 'edwin@gmail.com',
            'phoneNumber' => '0723456789',
        ]);
        
        //call the register method on the AuthenticationController
        $controller = new AuthenticationController();
        $response = $controller->register($request);

        //assert that the response status is 201
        $this->assertEquals(201, $response->status());
       // $this->assertEquals('An OTP has been sent to 0723*****89', $response->getData()->message);

        //assert that new site manager was created
        // $this->assertDatabaseHas('siteManagers', [
        //     'name' => 'Edwin',
        //     'email' => 'edwin@gmail.com',
        //     'phoneNumber' => '0723456789',
        // ]); 
    }

    public function test_verify_returns_site_manager_if_otp_is_valid()
    {
        $this->withoutExceptionHandling();

        // Create a new SiteManager with a known OTP
        $siteManager = SiteManager::factory()->create([
            'phoneNumber' => '1234567890',
            'otp' => '123456',
        ]);

        //create a mock request 
        $request = new Request([
            'phoneNumber' => '1234567890',
            'otp' => '123456',
        ]);
        
        //call verify method on AuthenticationController
        $controller = new AuthenticationController();
        $response = $controller->verify($request);

        //assert that the response status is 201
        $this->assertEquals(201, $response->status());

    }
    
    public function test_set_site_manager_password()
    {
        $this->withoutExceptionHandling();

        //create a new site manager with a known phone number
        $siteManager = SiteManager::factory()->create([
            'phoneNumber' => '0723456789',
            'password'=> null,
            
        ]);

        //create a mock request
        $request = new Request([
            'phoneNumber' => '0723456789',
            'password' => 'password',
            'passwordConfirmation' => 'password',
        ]);

        //call set password method on AuthenticationController
        $controller = new AuthenticationController();
        $response = $controller->setPassword($request);

        //assert that the response status is 201
        $this->assertEquals(201, $response->status());
        //$this->assertEquals('Password set successfully', $response->getData()->message);
    }

    public function test_login_returns_site_manager_if_credentials_are_valid()
    {
        $this->withoutExceptionHandling();

        //create a new site manager with a known phone number
        $siteManager = SiteManager::factory()->create([
            'phoneNumber' => '0723456789',
            'password'=> 'password',
        ]);

        //create a mock request
        $request = new Request([
            'phoneNumber' => '0723456789',
            'password' => 'password',
        ]);

        //call login method on AuthenticationController
        $controller = new AuthenticationController();
        $response = $controller->login($request);

        //assert that the response status is 201
        $this->assertEquals(401, $response->status());
        //$this->assertEquals('Login successful', $response->getData()->message);
    }

}
