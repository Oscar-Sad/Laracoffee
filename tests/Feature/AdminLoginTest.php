<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

        public function test_admin_can_login_successfully()
    {
        $role = Role::create([
            'role_name' => 'Admin'
        ]);

        $admin = User::create([
            'fullname' => 'Admin User',
            'username' => 'admin',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('password'),
            'role_id' => $role->id,
            'image' => 'default.png',
            'phone' => '0123456789',
            'gender' => 'M',
            'address' => 'Test Address',
            'coupon' => 0,
            'point' => 0,
        ]);

        $response = $this->post('/auth/login', [
            'email' => 'admin@gmail.com',
            'password' => 'password',
        ]);

        $response->assertStatus(302);

        $this->assertTrue(auth()->check());
    }
}
