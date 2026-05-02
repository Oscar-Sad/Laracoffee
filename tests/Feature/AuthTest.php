<?php

namespace Tests\Feature;

use App\Models\Role;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_register()
    {
        $role = Role::create([
            'role_name' => 'Admin'
        ]);

        $response = $this->post('/auth/register', [
            'fullname' => 'Oscar Tan',
            'username' => 'oscar123',
            'email' => 'oscar@gmail.com',
            'password' => '1234',
            'password_confirmation' => '1234',
            'phone' => '0123456789',
            'gender' => 'M',
            'address' => 'Bangi',
        ]);

        // Check redirect
        $response->assertRedirect('/auth/login');

        // Check database
        $this->assertDatabaseHas('users', [
            'email' => 'oscar@gmail.com',
            'username' => 'oscar123',
        ]);
    }

    /** @test */
    public function user_can_login()
    {
        // Create user first
        $user = User::create([
            'fullname' => 'Oscar Tan',
            'username' => 'oscar_login',
            'email' => 'login@gmail.com',
            'password' => Hash::make('1234'),
            'phone' => '0123456789',
            'gender' => 'M',
            'address' => 'Bangi',
            'image' => 'default.png',
            'role_id' => 2,
            'coupon' => 0,
            'point' => 0,
        ]);

        $response = $this->post('/auth/login', [
            'email' => 'login@gmail.com',
            'password' => '1234',
        ]);

        // Check redirect after login
        $response->assertRedirect('/home');

        // Check authenticated
        $this->assertAuthenticatedAs($user);
    }

    // /** @test */
    // public function login_fail_with_wrong_credentials()
    // {
    //     User::create([
    //         'fullname' => 'Oscar Tan',
    //         'username' => 'oscar123',
    //         'email' => 'oscar@gmail.com',
    //         'password' => Hash::make('1234'),
    //         'phone' => '0123456789',
    //         'gender' => 'M',
    //         'address' => 'Bangi',
    //         'image' => 'default.png',
    //         'role_id' => 2,
    //         'coupon' => 0,
    //         'point' => 0,
    //     ]);

    //     $response = $this->post('/auth/login', [
    //         'email' => 'oscar@gmail.com',
    //         'password' => 'wrongpassword',
    //     ]);

    //     $response->assertStatus(302); // redirect back
    //     $this->assertGuest();
    // }
}
